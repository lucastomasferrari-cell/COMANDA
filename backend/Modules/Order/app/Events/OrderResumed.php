<?php

namespace Modules\Order\Events;

use Modules\Order\Models\Order;

class OrderResumed
{
    public function __construct(public Order $order)
    {
    }
}
