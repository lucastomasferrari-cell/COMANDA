<?php

namespace Modules\AuditLog\Http\Controllers\Api\V1;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\AuditLog\Models\AuditLog;
use Modules\AuditLog\Models\PendingApproval;
use Modules\Core\Http\Controllers\Controller;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderProduct;
use Modules\Support\ApiResponse;

/**
 * Endpoint consolidado de métricas anti-fraude para el dashboard
 * admin. Filtros: date (default hoy), desde / hasta (ranges).
 *
 * Retorno:
 * - cards: totales + alertas boolean si cruzan umbrales.
 * - rankings: top 3 cajeros con más voids / descuentos.
 */
class AntiFraudDashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::today();
        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::today()->endOfDay();

        // Sales del período para calcular ratios.
        $salesTotal = (float) Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', [OrderStatus::Cancelled, OrderStatus::Refunded, OrderStatus::Merged])
            ->sum('total');

        // Voids
        $voidsCount = (int) OrderProduct::query()
            ->whereBetween('voided_at', [$from, $to])
            ->count();
        $voidsAmount = (float) OrderProduct::query()
            ->whereBetween('voided_at', [$from, $to])
            ->sum('total');
        $voidsRatio = $salesTotal > 0 ? round(($voidsAmount / $salesTotal) * 100, 2) : 0;

        // Descuentos (audit logs)
        $discountsCount = AuditLog::where('action', 'discount_applied')
            ->whereBetween('created_at', [$from, $to])
            ->count();
        $discountsAmount = (float) AuditLog::where('action', 'discount_applied')
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->sum(fn($log) => (float) ($log->new_values['value'] ?? 0));
        $discountsRatio = $salesTotal > 0 ? round(($discountsAmount / $salesTotal) * 100, 2) : 0;

        // Open items del período
        $openItemsCount = OrderProduct::query()
            ->whereNotNull('custom_name')
            ->whereBetween('created_at', [$from, $to])
            ->count();
        $openItemsAmount = (float) OrderProduct::query()
            ->whereNotNull('custom_name')
            ->whereBetween('created_at', [$from, $to])
            ->sum('total');

        // Pending approvals abiertos hoy
        $pendingApprovalsCount = PendingApproval::where('status', 'pending')
            ->count();

        // Cambios de forma de pago post-cobro
        $paymentChangesCount = AuditLog::where('action', 'payment_method_changed')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // Órdenes reopeneadas
        $reopenedOrders = AuditLog::where('action', 'order_reopened')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // Top 3 cajeros por voids — join directo con users sin relation.
        $topVoiders = DB::table('order_products')
            ->leftJoin('users', 'order_products.voided_by', '=', 'users.id')
            ->whereBetween('order_products.voided_at', [$from, $to])
            ->whereNotNull('order_products.voided_by')
            ->select(
                'order_products.voided_by as user_id',
                'users.name as name',
                DB::raw('COUNT(*) as count'),
            )
            ->groupBy('order_products.voided_by', 'users.name')
            ->orderByDesc('count')
            ->limit(3)
            ->get()
            ->map(fn($row) => [
                'user_id' => (int) $row->user_id,
                'name' => $row->name ?? "#{$row->user_id}",
                'count' => (int) $row->count,
            ]);

        return ApiResponse::success(body: [
            'period' => [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
            ],
            'cards' => [
                'sales_total' => $salesTotal,
                'voids' => [
                    'count' => $voidsCount,
                    'amount' => $voidsAmount,
                    'ratio_percent' => $voidsRatio,
                    'alert' => $voidsRatio > 3,
                ],
                'discounts' => [
                    'count' => $discountsCount,
                    'amount' => $discountsAmount,
                    'ratio_percent' => $discountsRatio,
                    'alert' => $discountsRatio > 10,
                ],
                'open_items' => [
                    'count' => $openItemsCount,
                    'amount' => $openItemsAmount,
                    'alert' => $openItemsCount > (int) setting('antifraud.open_item_max_total_per_shift_alert', 50),
                ],
                'pending_approvals' => $pendingApprovalsCount,
                'payment_method_changes' => $paymentChangesCount,
                'orders_reopened' => $reopenedOrders,
            ],
            'top_voiders' => $topVoiders,
        ]);
    }
}
