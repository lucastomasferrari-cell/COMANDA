<?php

namespace Modules\Pos\Services\PosCustomerViewer;

use Modules\Cart\CartCustomer;
use Modules\Cart\Facades\Cart;
use Modules\Cart\Models\Cart as CartModel;
use Modules\Loyalty\Models\LoyaltyCustomer;
use Modules\Loyalty\Models\LoyaltyProgram;
use Modules\Media\Transformers\Api\V1\MediaSimpleResource;
use Modules\User\Models\User;

class PosCustomerViewerService implements PosCustomerViewerServiceInterface
{
    /** @inheritDoc */
    public function snapshot(): array
    {
        return [
            'updated_at' => now()->timestamp,
            ...$this->payload()
        ];
    }

    private function payload(): array
    {
        $snapshot = $this->freshCart()->toArray();

        if (!is_null($snapshot['customer'])) {
            $snapshot['customer'] = $this->buildCustomerPayload($snapshot['customer']);
        }

        return $snapshot;
    }

    private function freshCart(): \Modules\Cart\Cart
    {
        return Cart::instance()->refresh();
    }

    private function buildCustomerPayload(CartCustomer $cartCustomer): ?array
    {
        $customer = User::query()->find($cartCustomer->id());

        if (is_null($customer)) {
            return null;
        }

        $program = LoyaltyProgram::query()->latest()->first();
        $loyaltyCustomer = null;

        if (!is_null($program)) {
            $loyaltyCustomer = LoyaltyCustomer::query()
                ->with(['loyaltyTier'])
                ->where('customer_id', $customer->id)
                ->where('loyalty_program_id', $program->id)
                ->first();
        }

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'tier' => !is_null($loyaltyCustomer) && !is_null($loyaltyCustomer->loyaltyTier) ? [
                'id' => $loyaltyCustomer->loyaltyTier->id,
                'name' => $loyaltyCustomer->loyaltyTier->name,
                'icon' => $loyaltyCustomer->loyaltyTier->icon != null
                    ? new MediaSimpleResource($loyaltyCustomer->loyaltyTier->icon)
                    : null,
            ] : null,
            'points_balance' => $loyaltyCustomer?->points_balance,
        ];
    }

    /** @inheritDoc */
    public function fingerprint(): string
    {
        return md5($this->cartUpdatedAtToken());
    }

    private function cartUpdatedAtToken(): string
    {
        $cartStorageKey = $this->cartStorageKey();
        $updatedAt = CartModel::query()
            ->select('updated_at')
            ->whereIn("id", [
                "{$cartStorageKey}_cart_items",
                "{$cartStorageKey}_cart_conditions",
            ])
            ->latest('updated_at')
            ->first()?->updated_at->toDateTimeString();

        return (string)($updatedAt ?? 'missing');
    }

    private function cartStorageKey(): string
    {
        return 'cart_' . request()->route('cartId');
    }
}
