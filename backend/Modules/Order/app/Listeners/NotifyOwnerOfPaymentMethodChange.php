<?php

namespace Modules\Order\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Events\OrderPaymentMethodChanged;
use Modules\Order\Mail\OwnerPaymentMethodChangedAlert;

/**
 * Listener del event OrderPaymentMethodChanged. Envía mail inmediato
 * al owner_alert_email configurado. Si el setting no existe o
 * daily_report_enabled está en false, loguea un warning.
 *
 * ShouldQueueAfterCommit para que el mail salga después del
 * DB::commit (evita mails que referencian filas aún en transaction).
 */
class NotifyOwnerOfPaymentMethodChange implements ShouldQueueAfterCommit
{
    public $tries = 3;
    public $backoff = 10;

    public function handle(OrderPaymentMethodChanged $event): void
    {
        // Gate by setting.
        if (!(bool) setting('antifraud.real_time_alerts.payment_method_change', true)) {
            return;
        }
        if (!(bool) setting('antifraud.daily_report_enabled', true)) {
            // Si el reporte diario está apagado, asumimos que el dueño
            // no quiere mails anti-fraude. Coherente con Bloque 11.
            return;
        }

        $email = setting('antifraud.owner_alert_email');
        if (empty($email)) {
            Log::warning('OrderPaymentMethodChanged alert skipped — no owner_alert_email configured.', [
                'order_id' => $event->order->id,
            ]);
            return;
        }

        Mail::to($email)->send(new OwnerPaymentMethodChangedAlert(
            order: $event->order,
            paymentId: $event->paymentId,
            oldMethod: $event->oldMethod,
            newMethod: $event->newMethod,
            reason: $event->reason,
            approverUserId: $event->approverUserId,
        ));
    }
}
