<?php

namespace Modules\Payment\Services\PaymentMethodItem;

use App\Forkiva;
use Arr;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Modules\Payment\Enums\PaymentMethod;
use Modules\Payment\Models\Payment;
use Modules\Payment\Models\PaymentMethodItem;
use Modules\Support\GlobalStructureFilters;

class PaymentMethodItemService
{
    public function label(): string
    {
        return __('payment::payment_methods.payment_method');
    }

    public function getModel(): PaymentMethodItem
    {
        return new ($this->model());
    }

    public function model(): string
    {
        return PaymentMethodItem::class;
    }

    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return $this->getModel()
            ->query()
            ->withoutGlobalActive()
            ->filters($filters)
            ->sortBy($sorts)
            ->orderBy('order')
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }

    public function show(int $id): PaymentMethodItem
    {
        return $this->findOrFail($id);
    }

    public function findOrFail(int $id): Builder|array|EloquentCollection|PaymentMethodItem
    {
        return $this->getModel()
            ->query()
            ->withoutGlobalActive()
            ->findOrFail($id);
    }

    public function store(array $data): PaymentMethodItem
    {
        return $this->getModel()->query()->create($data);
    }

    public function update(int $id, array $data): PaymentMethodItem
    {
        $item = $this->findOrFail($id);
        $item->update($data);
        return $item;
    }

    public function destroy(int|array|string $ids): bool
    {
        return $this->getModel()
            ->query()
            ->withoutGlobalActive()
            ->whereIn('id', parseIds($ids))
            ->delete() ?: false;
    }

    public function getStructureFilters(): array
    {
        return [
            GlobalStructureFilters::active(),
            [
                'key' => 'type',
                'label' => __('payment::attributes.payment_methods.type'),
                'type' => 'select',
                'options' => PaymentMethod::toArrayTrans(),
            ],
        ];
    }

    public function getFormMeta(): array
    {
        return [
            'types' => PaymentMethod::toArrayTrans(),
        ];
    }

    /**
     * Reporte agregado de ventas por forma de pago en un rango.
     * Agrupa payments.method (enum value) con join a payment_methods
     * por type. Si hay múltiples rows con mismo type, toma la más
     * antigua (id ASC) como representante — scope parking: agregar
     * payment_method_id FK a payments cuando se necesite distinguir.
     */
    public function report(Carbon $from, Carbon $to): array
    {
        $rows = DB::table('payments')
            ->selectRaw('method, SUM(amount) AS total_amount, COUNT(*) AS transactions_count')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->whereNull('deleted_at')
            ->groupBy('method')
            ->get();

        $methodLookup = PaymentMethodItem::query()
            ->withoutGlobalActive()
            ->orderBy('id')
            ->get()
            ->groupBy(fn($item) => $item->type->value)
            ->map(fn($group) => $group->first());

        return $rows->map(function ($row) use ($methodLookup) {
            $item = $methodLookup[$row->method] ?? null;
            return [
                'method_id' => $item?->id,
                'method_name' => $item?->name ?? __('payment::enums.payment_methods.' . $row->method),
                'method_type' => $row->method,
                'total_amount' => (float) $row->total_amount,
                'transactions_count' => (int) $row->transactions_count,
            ];
        })->sortByDesc('total_amount')->values()->all();
    }
}
