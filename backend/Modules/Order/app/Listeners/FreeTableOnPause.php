<?php

namespace Modules\Order\Listeners;

use Modules\Order\Enums\OrderType;
use Modules\Order\Events\OrderPaused;
use Modules\SeatingPlan\Enums\TableStatus;

/**
 * Cuando una orden dine_in se pausa, la mesa queda available —
 * el cliente se levantó o cambio de mesa temporalmente. La orden
 * persiste y se puede reanudar (OrderResumed) desde el ActiveOrders.
 */
class FreeTableOnPause
{
    public function handle(OrderPaused $event): void
    {
        $order = $event->order;

        if ($order->type !== OrderType::DineIn || is_null($order->table_id)) {
            return;
        }

        $table = $order->table;
        if (!$table) {
            return;
        }

        if ($table->status !== TableStatus::Available) {
            $table->update(["status" => TableStatus::Available]);
            $table->storeStatusLog(
                status: TableStatus::Available,
                note: "ORDER_PAUSED :{$order->reference_no}",
            );
        }
    }
}
