<?php

namespace Modules\Order\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\Order\Models\VoidReason;
use Modules\Support\ApiResponse;

/**
 * Catálogo read-only de razones para anular items / órdenes.
 * El admin las puede CRUDear desde un panel futuro; hoy el POS
 * solo las consume.
 */
class VoidReasonController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $appliesTo = $request->input('applies_to', 'item');

        $reasons = VoidReason::query()
            ->where('is_active', true)
            ->when(
                in_array($appliesTo, ['item', 'order'], true),
                fn($q) => $q->whereIn('applies_to', [$appliesTo, 'both']),
            )
            ->orderBy('order')
            ->get(['id', 'code', 'name', 'applies_to', 'requires_manager_approval']);

        return ApiResponse::success(
            body: $reasons->map(fn($r) => [
                'id' => $r->id,
                'code' => $r->code,
                'name' => $r->name,
                'applies_to' => $r->applies_to,
                'requires_manager_approval' => $r->requires_manager_approval,
            ])->all(),
        );
    }
}
