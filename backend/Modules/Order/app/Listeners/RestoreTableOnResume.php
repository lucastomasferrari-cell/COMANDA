<?php

namespace Modules\Order\Listeners;

use Modules\Order\Enums\OrderType;
use Modules\Order\Events\OrderResumed;
use Modules\SeatingPlan\Enums\TableStatus;

/**
 * Al reanudar una orden pausada, la mesa vuelve a ocupada. Si otra
 * orden esta activa sobre la misma mesa, no sobreescribimos — el
 * TableViewerService maneja mergeos.
 */
class RestoreTableOnResume
{
    public function handle(OrderResumed $event): void
    {
        $order = $event->order;

        if ($order->type !== OrderType::DineIn || is_null($order->table_id)) {
            return;
        }

        $table = $order->table;
        if (!$table) {
            return;
        }

        if ($table->status === TableStatus::Available) {
            $table->update(["status" => TableStatus::Occupied]);
            $table->storeStatusLog(
                status: TableStatus::Occupied,
                note: "ORDER_RESUMED :{$order->reference_no}",
            );
        }
    }
}
