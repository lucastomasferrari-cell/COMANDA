<?php

namespace Modules\Order\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Loyalty\Services\Loyalty\LoyaltyService;
use Modules\Loyalty\Services\Loyalty\LoyaltyServiceInterface;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Events\OrderUpdateStatus;
use Throwable;

class HandleOrderLoyaltyPoints implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    /** @var array<int> Backoff en segundos: 10s, 30s, 2min. */
    public array $backoff = [10, 30, 120];

    public function handle(OrderUpdateStatus $event): void
    {
        $order = $event->order;
        $status = $event->status;

        try {
            /** @var LoyaltyService $loyalty */
            $loyalty = app(LoyaltyServiceInterface::class);

            if ($status === OrderStatus::Completed) {
                $loyalty->earnForOrder($order);
            } else if (in_array($status, [OrderStatus::Cancelled, OrderStatus::Refunded])) {
                $partialAmount = null;
                if ($status === OrderStatus::Refunded && property_exists($order, 'return_amount')) {
                    $partialAmount = $order->return_amount;
                }

                $loyalty->cancelForOrder($order, ['partial_amount' => $partialAmount]);
            }
        } catch (Throwable $e) {
            // Log con contexto y re-throw para que Laravel retry (3 intentos
            // con backoff). Si agotamos retries cae en failed_jobs.
            Log::error('HandleOrderLoyaltyPoints failed', [
                'order_id' => $order->id,
                'status' => $status->value,
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'attempt' => $this->job?->attempts() ?? 0,
            ]);
            throw $e;
        }
    }
}
