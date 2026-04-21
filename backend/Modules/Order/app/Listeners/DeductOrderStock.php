<?php

namespace Modules\Order\Listeners;

use Modules\Inventory\Services\StockSync\StockSyncServiceInterface;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Events\OrderUpdateStatus;
use Throwable;

class DeductOrderStock
{
    /**
     * Handle the event.
     */
    public function handle(OrderUpdateStatus $event): void
    {
        if ($event->status === OrderStatus::Completed) {
            try {
                app(StockSyncServiceInterface::class)->deductOrderStock($event->order);
                $event->order->update(["is_stock_deducted" => true]);
            } catch (Throwable) {
            }
        }
    }
}
