<?php

namespace Modules\Order\Listeners;

use Modules\Inventory\Services\StockSync\StockSyncServiceInterface;
use Modules\Order\Events\OrderVoided;
use Throwable;

class RestoreOrderStock
{
    /**
     * Handle the event.
     */
    public function handle(OrderVoided $event): void
    {
        if ($event->order->is_stock_deducted) {
            try {
                app(StockSyncServiceInterface::class)->restoreOrderStock($event->order);
                $event->order->update(["is_stock_deducted" => false]);
            } catch (Throwable) {
            }
        }
    }
}
