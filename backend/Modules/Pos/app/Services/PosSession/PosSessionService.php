<?php

namespace Modules\Pos\Services\PosSession;

use App\Forkiva;
use DB;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Modules\AuditLog\Services\AuditLogger;
use Modules\Branch\Models\Branch;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;
use Modules\Pos\Enums\PosSessionStatus;
use Modules\User\Models\User;
use Modules\User\Services\Auth\ManagerPinService;
use Modules\Pos\Events\ClosePosSession;
use Modules\Pos\Events\OpenPosSession;
use Modules\Pos\Models\PosRegister;
use Modules\Pos\Models\PosSession;
use Modules\Support\GlobalStructureFilters;

class PosSessionService implements PosSessionServiceInterface
{
    /** @inheritDoc */
    public function label(): string
    {
        return __("pos::pos_sessions.pos_session");
    }

    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return $this->getModel()
            ->query()
            ->with([
                "branch:id,name,currency",
                "openedBy",
                "closedBy",
                "posRegister"
            ])
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }

    /** @inheritDoc */
    public function getModel(): PosSession
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return PosSession::class;
    }

    /** @inheritDoc */
    public function show(int $id): PosSession
    {
        return $this->getModel()
            ->query()
            ->with([
                "branch:id,name,currency",
                "openedBy",
                "closedBy",
                "posRegister"
            ])
            ->findOrFail($id);
    }

    /** @inheritDoc */
    public function findOrFail(int $id): Builder|array|EloquentCollection|PosSession
    {
        return $this->getModel()
            ->query()
            ->with(["openedBy", "closedBy", "branch"])
            ->findOrFail($id);
    }

    /** @inheritDoc */
    public function open(array $data): PosSession
    {
        return DB::transaction(function () use ($data) {
            $register = PosRegister::query()
                ->whereBranch($data['branch_id'])
                ->findOrFail($data['pos_register_id']);

            // lockForUpdate toma gap-locks sobre el indice (branch, register, status)
            // bloqueando inserciones concurrentes de otra sesion abierta en la
            // misma caja hasta que termine esta transaccion.
            $existing = PosSession::query()
                ->whereBranch($data['branch_id'])
                ->where('pos_register_id', $register->id)
                ->where('status', PosSessionStatus::Open)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'pos_register_id' => __('pos::messages.session_already_opened'),
                ]);
            }

            try {
                $session = PosSession::query()
                    ->create([
                        'branch_id' => $data['branch_id'],
                        'pos_register_id' => $register->id,
                        'opened_by' => auth()->id(),
                        'status' => PosSessionStatus::Open,
                        'opened_at' => now(),
                        'opening_float' => $data['opening_float'] ?? 0,
                        'notes' => $data['notes'] ?? null,
                    ]);
            } catch (QueryException $e) {
                // Defense in depth: si el lockForUpdate no alcanzo (p.ej. isolation
                // distinto), el unique index uniq_open_pos_session atrapa el
                // duplicado. Traducimos el error 1062 al mismo mensaje amigable.
                if (($e->errorInfo[1] ?? null) === 1062) {
                    throw ValidationException::withMessages([
                        'pos_register_id' => __('pos::messages.session_already_opened'),
                    ]);
                }
                throw $e;
            }

            event(new OpenPosSession($session));
            return $session;
        });
    }

    /** @inheritDoc */
    public function close(int $id, array $data): PosSession
    {
        /** @var PosSession $session */
        $session = $this->findOrFail($id);

        return DB::transaction(function () use ($session, $data) {

            abort_if($session->status !== PosSessionStatus::Open, 400, __('pos::messages.session_closed'));

            // Hard lock: no cerrar session si hay ordenes abiertas en el
            // register. El cajero debe resolver (cobrar / anular / mover).
            $openOrdersCount = Order::query()
                ->where('pos_register_id', $session->pos_register_id)
                ->whereNotIn('status', [
                    OrderStatus::Completed,
                    OrderStatus::Cancelled,
                    OrderStatus::Refunded,
                    OrderStatus::Merged,
                ])
                ->count();

            abort_if(
                $openOrdersCount > 0,
                422,
                __('pos::messages.session_close_blocked_by_open_orders', ['count' => $openOrdersCount]),
            );

            $session->declared_cash = $data['declared_cash'];

            try {
                $summary = $session->calculateClosingSummary();
            } catch (Exception $exception) {
                throw ValidationException::withMessages([
                    "declared_cash" => $exception->getMessage(),
                ]);
            }

            // Validacion anti-fraude (Bloque 12):
            // - |diff| > justification_threshold → justification 20+ chars obligatoria.
            // - |diff| > manager_threshold → manager_approval_token obligatorio.
            // El branch.cash_difference_threshold ya aborta el cierre antes
            // (via exception en calculateClosingSummary) — nuestros umbrales
            // viven entre 0 y ese techo.
            $expected = (float) ($summary['system_cash_sales'] ?? 0);
            $actual = (float) $data['declared_cash'];
            $diff = abs($actual - $expected);

            $justificationMinAmount = (float) setting('antifraud.session_close_justification_threshold', 500);
            $managerThresholdPercent = (float) setting('antifraud.session_close_manager_required_percent', 10);
            $managerThresholdAmount = $expected > 0 ? $expected * ($managerThresholdPercent / 100) : PHP_FLOAT_MAX;

            $approverUserId = null;
            $justification = $data['justification'] ?? null;

            if ($diff >= $justificationMinAmount) {
                abort_if(
                    empty($justification) || strlen($justification) < 20,
                    422,
                    __('pos::messages.session_close_justification_required', ['diff' => number_format($diff, 2)]),
                );
            }

            if ($diff >= $managerThresholdAmount && $expected > 0) {
                $token = $data['manager_approval_token'] ?? null;
                abort_unless(
                    $token,
                    403,
                    __('pos::messages.session_close_manager_required', [
                        'diff' => number_format($diff, 2),
                        'threshold' => number_format($managerThresholdAmount, 2),
                    ]),
                );

                $payload = app(ManagerPinService::class)->consumeToken($token);
                abort_unless(
                    $payload && !empty($payload['user_id']),
                    403,
                    __('pos::messages.session_close_token_invalid'),
                );

                $approver = User::find($payload['user_id']);
                abort_unless(
                    $approver && $approver->can('admin.pos_sessions.close'),
                    403,
                    __('pos::messages.session_close_approver_lacks_permission'),
                );

                $approverUserId = $approver->id;
            }

            $session->update([
                'closed_by' => auth()->id(),
                'closed_at' => now(),
                'status' => PosSessionStatus::Closed,
                'declared_cash' => $data['declared_cash'],
                ...$summary
            ]);

            AuditLogger::log('pos_session_closed', $session, [
                'reason' => $justification,
                'new_values' => [
                    'expected_cash' => $expected,
                    'declared_cash' => $actual,
                    'difference' => $actual - $expected,
                    'open_orders_at_close' => 0,
                ],
                'metadata' => [
                    'absolute_diff' => $diff,
                    'justification_threshold' => $justificationMinAmount,
                    'manager_threshold_amount' => $managerThresholdAmount,
                    'required_justification' => $diff >= $justificationMinAmount,
                    'required_manager' => $diff >= $managerThresholdAmount,
                ],
                'approved_by' => $approverUserId,
                'is_fiscal' => false,
            ]);

            event(new ClosePosSession($session));

            return $session;
        });
    }


    /** @inheritDoc */
    public function getStructureFilters(?int $branchId = null): array
    {
        $branchFilter = GlobalStructureFilters::branch();
        return [
            ...(is_null($branchFilter) ? [] : [$branchFilter]),
            [
                "key" => 'pos_register_id',
                "label" => __('pos::pos_sessions.filters.pos_register'),
                "type" => 'select',
                "options" => !is_null($branchId) ? PosRegister::list($branchId) : [],
                "depends" => "branch_id"
            ],
            [
                "key" => 'status',
                "label" => __('pos::pos_sessions.filters.status'),
                "type" => 'select',
                "options" => PosSessionStatus::toArrayTrans()
            ],
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }


    /** @inheritDoc */
    public function getFormMeta(?int $branchId = null): array
    {
        if (is_null($branchId)) {
            return [
                "branches" => Branch::list(),
            ];
        } else {
            return [
                "pos_registers" => PosRegister::list($branchId),
            ];
        }
    }
}
