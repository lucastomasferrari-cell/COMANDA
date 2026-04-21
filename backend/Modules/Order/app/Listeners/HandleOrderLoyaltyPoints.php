<?php

namespace Modules\Order\Listeners;

use Log;
use Modules\Loyalty\Services\Loyalty\LoyaltyService;
use Modules\Loyalty\Services\Loyalty\LoyaltyServiceInterface;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Events\OrderUpdateStatus;
use Throwable;

class HandleOrderLoyaltyPoints
{
    /**
     * Handle the event.
     *
     * @param OrderUpdateStatus $event
     */
    public function handle(OrderUpdateStatus $event): void
    {
        $order = $event->order;
        $status = $event->status;

        try {
            /** @var LoyaltyService $loyalty */
            $loyalty = app(LoyaltyServiceInterface::class);

            if ($status === OrderStatus::Completed) {
                $loyalty->earnForOrder($order);
            } else if (in_array($status, [OrderStatus::Cancelled, OrderStatus::Refunded])) {
                $partialAmount = null;
                if ($status === OrderStatus::Refunded && property_exists($order, 'return_amount')) {
                    $partialAmount = $order->return_amount;
                }

                $loyalty->cancelForOrder($order, ['partial_amount' => $partialAmount]);
            }
        } catch (Throwable $e) {
            Log::error('Loyalty points processing failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'status' => $status->value,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
