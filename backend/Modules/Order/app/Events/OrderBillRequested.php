<?php

namespace Modules\Order\Events;

use Modules\Order\Models\Order;

class OrderBillRequested
{
    public function __construct(public Order $order)
    {
    }
}
