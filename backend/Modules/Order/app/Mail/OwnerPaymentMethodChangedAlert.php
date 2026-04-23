<?php

namespace Modules\Order\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Order\Models\Order;

class OwnerPaymentMethodChangedAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public int $paymentId,
        public string $oldMethod,
        public string $newMethod,
        public string $reason,
        public int $approverUserId,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Alerta] Cambio de forma de pago #{$this->order->reference_no}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'order::mail.payment_method_changed_alert',
            with: [
                'order' => $this->order,
                'paymentId' => $this->paymentId,
                'oldMethod' => $this->oldMethod,
                'newMethod' => $this->newMethod,
                'reason' => $this->reason,
                'approverUserId' => $this->approverUserId,
            ],
        );
    }
}
