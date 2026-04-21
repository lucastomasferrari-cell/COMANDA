<?php

namespace Modules\Order\Events;

use Modules\Order\Models\Order;

class OrderUpdated
{
    /**
     * Create a new event instance.
     *
     * @param Order $order
     * @param Order|null $oldOrder
     */
    public function __construct(public Order $order, public ?Order $oldOrder = null)
    {
    }
}
