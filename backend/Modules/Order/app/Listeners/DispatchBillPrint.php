<?php

namespace Modules\Order\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Order\Events\OrderBillRequested;

/**
 * Stub: en Argentina el flujo esperado al marcar "cuenta pedida" es
 * que la impresora fiscal imprima automaticamente el ticket de
 * cuenta. Hoy el modulo Printer no expone job dedicado para bill, y
 * OrderPaymentService::dispatchPrint es a su vez stub vendor.
 *
 * No-op + log — cuando se implemente PrintBillJob real, este
 * listener lo despacha.
 */
class DispatchBillPrint
{
    public function handle(OrderBillRequested $event): void
    {
        Log::info('OrderBillRequested listener stub — print dispatch not implemented yet', [
            'order_id' => $event->order->id,
            'order_reference_no' => $event->order->reference_no,
            'table_id' => $event->order->table_id,
        ]);
    }
}
