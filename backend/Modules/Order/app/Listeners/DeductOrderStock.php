<?php

namespace Modules\Order\Listeners;

use Illuminate\Support\Facades\Log;
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
            } catch (Throwable $e) {
                // No re-throw: romper la confirmacion del pedido por un
                // problema de stock (ingrediente sin unit, metadata
                // inconsistente, etc.) frena ventas validas. En su lugar,
                // log detallado para diagnostico + is_stock_deducted queda
                // en false para que reconciliacion pueda detectarlo.
                Log::error('DeductOrderStock failed', [
                    'order_id' => $event->order->id,
                    'order_reference_no' => $event->order->reference_no,
                    'branch_id' => $event->order->branch_id,
                    'status' => $event->status->value,
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }
}
