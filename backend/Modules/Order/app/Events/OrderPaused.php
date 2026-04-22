<?php

namespace Modules\Order\Events;

use Modules\Order\Models\Order;

class OrderPaused
{
    public function __construct(public Order $order)
    {
    }
}
