<?php

namespace Modules\Order\Events;

use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;

class OrderUpdateStatus
{
    /**
     * Create a new event instance.
     *
     * @param Order $order
     * @param OrderStatus $status
     * @param int|null $reasonId
     * @param int|null $changedById
     * @param string|null $note
     * @param bool $stopUpdateOrderProductStatus
     */
    public function __construct(
        public Order       $order,
        public OrderStatus $status,
        public ?int        $reasonId = null,
        public ?int        $changedById = null,
        public ?string     $note = null,
        public bool        $stopUpdateOrderProductStatus = false,
    )
    {
    }
}
