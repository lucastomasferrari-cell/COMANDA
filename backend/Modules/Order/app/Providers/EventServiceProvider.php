<?php

namespace Modules\Order\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Order\Events\OrderBillRequested;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderMergeBillingPaid;
use Modules\Order\Events\OrderPaid;
use Modules\Order\Events\OrderPaused;
use Modules\Order\Events\OrderPaymentMethodChanged;
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
        OrderPaymentMethodChanged::class => [
            // Notifica al owner por email de forma inmediata. El
            // AuditLog ya lo registró el service. Respeta los
            // settings antifraud.real_time_alerts.*.
            \Modules\Order\Listeners\NotifyOwnerOfPaymentMethodChange::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
        // Registra el namespace de vistas 'order' para que Mail
        // pueda usar view('order::mail.payment_method_changed_alert').
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'order');
    }
}
