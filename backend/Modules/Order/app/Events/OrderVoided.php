<?php

namespace Modules\Order\Events;

use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;
use Modules\Payment\Enums\RefundPaymentMethod;
use Modules\Pos\Models\PosSession;

class OrderVoided
{
    /**
     * Create a new event instance.
     *
     * @param Order $order
     * @param OrderStatus $status
     * @param RefundPaymentMethod|null $refundPaymentMethod
     * @param PosSession|null $posSession
     * @param string|null $note
     * @param string|null $giftCardCode
     */
    public function __construct(
        public Order                $order,
        public OrderStatus          $status,
        public ?RefundPaymentMethod $refundPaymentMethod = null,
        public ?PosSession          $posSession = null,
        public ?string              $note = null,
        public ?string              $giftCardCode = null,
    )
    {
    }
}
