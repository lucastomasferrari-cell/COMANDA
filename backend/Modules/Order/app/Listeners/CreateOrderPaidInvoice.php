<?php

namespace Modules\Order\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Invoice\Services\CreateInvoice\CreateInvoiceServiceInterface;
use Modules\Order\Events\OrderMergeBillingPaid;
use Modules\Order\Events\OrderPaid;
use Modules\Order\Models\Order;
use Throwable;

class CreateOrderPaidInvoice implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    /** @var array<int> Backoff en segundos: 10s, 30s, 2min. */
    public array $backoff = [10, 30, 120];

    /**
     * Handle the event.
     *
     * @throws Throwable
     */
    public function handle(OrderPaid|OrderMergeBillingPaid $event): void
    {
        $order = $event instanceof OrderMergeBillingPaid
            ? Order::query()->where('table_merge_id', $event->tableMerge->id)->first()
            : $event->order;

        app(CreateInvoiceServiceInterface::class)->create($order);
    }
}
