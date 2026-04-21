<?php

namespace Modules\Order\Listeners;

use Modules\Currency\Models\CurrencyRate;
use Modules\GiftCard\Services\GiftCard\GiftCardServiceInterface;
use Modules\Order\Events\OrderVoided;
use Modules\Payment\Enums\PaymentMethod;
use Modules\Payment\Enums\PaymentType;
use Throwable;

class OrderRefundAmount
{
    /**
     * Handle the event.
     *
     * @param OrderVoided $event
     *
     * @throws Throwable
     */
    public function handle(OrderVoided $event): void
    {
        if ($event->order->hasRefundAmount()) {
            $paymentData = [
                "type" => PaymentType::Refund->value,
                'cashier_id' => auth()->id(),
                'method' => $event->refundPaymentMethod?->value,
                'amount' => $event->order->getRefundedAmount()->amount(),
                'currency_rate' => CurrencyRate::for($event->order->currency),
                'session' => $event->posSession,
                'notes' => $event->note
            ];

            if ($event->refundPaymentMethod?->value === PaymentMethod::GiftCard->value && $event->giftCardCode) {
                $giftCard = app(GiftCardServiceInterface::class)->findOrFail($event->giftCardCode);
                $paymentData['meta'] = [
                    'gift_card_code' => $giftCard->code,
                    'gift_card_id' => $giftCard->id,
                ];
            }

            $event->order->storePayment($paymentData);
        }
    }
}
