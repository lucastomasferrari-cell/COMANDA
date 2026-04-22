<?php

namespace Modules\AuditLog\Http\Controllers\Api\V1;

use App\Forkiva;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\AuditLog\Models\AuditLog;
use Modules\AuditLog\Transformers\Api\V1\AuditLogResource;
use Modules\Core\Http\Controllers\Controller;
use Modules\Support\ApiResponse;

class AuditLogController extends Controller
{
    /**
     * Lista paginada de audit logs con filtros.
     *
     * Query params:
     * - start_date, end_date: rango de created_at (ISO-8601 o fecha).
     * - user_id: acciones disparadas por un usuario.
     * - action: nombre exacto (ej. void_item).
     * - auditable_type, auditable_id: historial de una entidad.
     * - is_fiscal: boolean.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()->with(['user:id,name', 'approver:id,name']);

        $this->applyFilters($query, $request);

        $paginator = $query
            ->orderByDesc('created_at')
            ->paginate(Forkiva::paginate())
            ->withQueryString();

        return ApiResponse::pagination(
            paginator: $paginator,
            resource: AuditLogResource::class,
        );
    }

    public function show(int $id): JsonResponse
    {
        $log = AuditLog::with(['user:id,name', 'approver:id,name', 'parent'])
            ->findOrFail($id);

        return ApiResponse::success(
            body: new AuditLogResource($log),
        );
    }

    protected function applyFilters(Builder $query, Request $request): void
    {
        if ($start = $request->input('start_date')) {
            $query->where('created_at', '>=', $start);
        }
        if ($end = $request->input('end_date')) {
            $query->where('created_at', '<=', $end);
        }
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }
        if ($type = $request->input('auditable_type')) {
            $query->where('auditable_type', $type);
        }
        if ($id = $request->input('auditable_id')) {
            $query->where('auditable_id', $id);
        }
        if ($request->has('is_fiscal')) {
            $query->where('is_fiscal', filter_var(
                $request->input('is_fiscal'),
                FILTER_VALIDATE_BOOLEAN,
            ));
        }
    }
}
