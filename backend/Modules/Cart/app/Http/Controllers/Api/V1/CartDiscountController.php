<?php

namespace Modules\Cart\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\AuditLog\Services\AuditLogger;
use Modules\Cart\Facades\Cart;
use Modules\Cart\Services\DiscountApplyService\DiscountApplyServiceInterface;
use Modules\Core\Http\Controllers\Controller;
use Modules\Discount\Models\Discount;
use Modules\Support\ApiResponse;
use Modules\Support\Enums\PriceType;
use Modules\User\Models\User;
use Modules\User\Services\Auth\ManagerPinService;

class CartDiscountController extends Controller
{
    /**
     * Apply discount to cart.
     *
     * Enforcement anti-fraude (Bloque 4): si el % del descuento
     * supera el umbral del cashier, se requiere manager_approval_token
     * válido. Si además supera el umbral del manager, el approver
     * debe tener admin.orders.discount_large.
     */
    public function store(
        DiscountApplyServiceInterface $service,
        Request $request,
        ManagerPinService $pinService,
        string $cartId,
        int $id,
    ): JsonResponse {
        $request->validate([
            'manager_approval_token' => 'nullable|string',
        ]);

        $discount = Discount::query()->findOrFail($id);

        $percent = $this->effectivePercent($discount);
        $cashierMax = (float) setting('antifraud.discount_cashier_max_percent', 10);
        $managerMax = (float) setting('antifraud.discount_manager_max_percent', 25);

        $needsApproval = $percent > $cashierMax;
        $needsLargeTier = $percent > $managerMax;

        $approverUserId = null;

        if ($needsApproval) {
            $token = $request->input('manager_approval_token');
            $payload = $token ? $pinService->consumeToken($token) : null;

            abort_unless(
                $payload && !empty($payload['user_id']),
                403,
                __('cart::messages.discount_requires_approval', ['limit' => $cashierMax]),
            );

            $approver = User::find($payload['user_id']);
            abort_unless(
                $approver,
                403,
                __('cart::messages.approver_not_found'),
            );

            $requiredPerm = $needsLargeTier ? 'admin.orders.discount_large' : 'admin.orders.discount_medium';
            abort_unless(
                $approver->can($requiredPerm),
                403,
                __('cart::messages.approver_lacks_permission', ['permission' => $requiredPerm]),
            );

            $approverUserId = $approver->id;
        }

        $service->applyDiscount(Cart::class, $id);

        AuditLogger::log('discount_applied', $discount, [
            'reason' => $discount->name ?? null,
            'new_values' => [
                'discount_id' => $discount->id,
                'discount_name' => $discount->name ?? null,
                'type' => $discount->type?->value ?? $discount->type,
                'value' => (float) ($discount->value ?? 0),
                'effective_percent' => $percent,
            ],
            'metadata' => [
                'cart_id' => $cartId,
                'cashier_max' => $cashierMax,
                'manager_max' => $managerMax,
                'needs_approval' => $needsApproval,
                'needs_large_tier' => $needsLargeTier,
            ],
            'approved_by' => $approverUserId,
        ]);

        return ApiResponse::success(Cart::instance());
    }

    /**
     * Remove discount from cart.
     */
    public function destroy(): JsonResponse
    {
        Cart::removeDiscount();

        return ApiResponse::success(Cart::instance());
    }

    /**
     * % efectivo del discount. Para type=percent es el valor
     * directo; para type=fixed calcula % sobre subtotal del cart
     * actual. Subtotal 0 → 0 (defensa contra división por cero).
     */
    protected function effectivePercent(Discount $discount): float
    {
        $type = is_object($discount->type) ? $discount->type->value : $discount->type;
        $value = (float) ($discount->value ?? 0);
        if ($type === PriceType::Percent->value || $type === 'percent') {
            return $value;
        }
        $subtotal = Cart::subTotal()?->amount() ?? 0;
        if ($subtotal <= 0) return 0;
        return round(($value / $subtotal) * 100, 2);
    }
}
