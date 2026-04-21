<?php

namespace Modules\Order\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Services\StockSync\StockSyncServiceInterface;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Events\OrderUpdateStatus;
use Throwable;

class DeductOrderStock implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    /** @var array<int> Backoff en segundos: 10s, 30s, 2min. */
    public array $backoff = [10, 30, 120];

    public function handle(OrderUpdateStatus $event): void
    {
        if ($event->status !== OrderStatus::Completed) {
            return;
        }

        try {
            app(StockSyncServiceInterface::class)->deductOrderStock($event->order);
            $event->order->update(["is_stock_deducted" => true]);
        } catch (Throwable $e) {
            // Log con contexto completo antes de re-throw para que quede
            // rastreable aunque el job termine en failed_jobs.
            Log::error('DeductOrderStock failed', [
                'order_id' => $event->order->id,
                'order_reference_no' => $event->order->reference_no,
                'branch_id' => $event->order->branch_id,
                'status' => $event->status->value,
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'attempt' => $this->job?->attempts() ?? 0,
            ]);
            throw $e;
        }
    }
}
