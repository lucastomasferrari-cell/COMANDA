<?php

namespace Modules\Order\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Order\Events\OrderBillRequested;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderMergeBillingPaid;
use Modules\Order\Events\OrderPaid;
use Modules\Order\Events\OrderPaused;
use Modules\Order\Events\OrderResumed;
use Modules\Order\Events\OrderUpdated;
use Modules\Order\Events\OrderUpdateStatus;
use Modules\Order\Events\OrderVoided;
use Modules\Order\Listeners\CreateOrderCreditNote;
use Modules\Order\Listeners\CreateOrderPaidInvoice;
use Modules\Order\Listeners\DeductOrderStock;
use Modules\Order\Listeners\DispatchBillPrint;
use Modules\Order\Listeners\FreeTableOnPause;
use Modules\Order\Listeners\HandleOrderLoyaltyPoints;
use Modules\Order\Listeners\MarkTableAsFree;
use Modules\Order\Listeners\OrderRefundAmount;
use Modules\Order\Listeners\RestoreOrderStock;
use Modules\Order\Listeners\RestoreTableOnResume;
use Modules\Order\Listeners\StoreOrderStatusLogo;
use Modules\Order\Listeners\UpdateOrderProductStatus;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderUpdateStatus::class => [
            StoreOrderStatusLogo::class,
            MarkTableAsFree::class,
            DeductOrderStock::class,
            UpdateOrderProductStatus::class,
            HandleOrderLoyaltyPoints::class,
        ],
        OrderVoided::class => [
            OrderRefundAmount::class,
            RestoreOrderStock::class,
            CreateOrderCreditNote::class,
        ],
        OrderCreated::class => [
        ],
        OrderUpdated::class => [],
        OrderPaid::class => [
            CreateOrderPaidInvoice::class,
        ],
        OrderMergeBillingPaid::class => [
            CreateOrderPaidInvoice::class,
        ],
        OrderBillRequested::class => [
            DispatchBillPrint::class,
        ],
        OrderPaused::class => [
            FreeTableOnPause::class,
        ],
        OrderResumed::class => [
            RestoreTableOnResume::class,
        ],
    ];
}
