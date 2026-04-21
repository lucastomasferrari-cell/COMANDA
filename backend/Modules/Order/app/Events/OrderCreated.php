<?php

namespace Modules\Order\Events;

use Modules\Order\Models\Order;

class OrderCreated
{
    /**
     * Create a new event instance.
     *
     * @param Order $order
     */
    public function __construct(public Order $order)
    {
    }
}
