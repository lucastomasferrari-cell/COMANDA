<?php

namespace Modules\Order\Services\OrderPayment;

use DB;
use Illuminate\Support\Collection;
use Modules\Currency\Currency;
use Modules\GiftCard\Enums\GiftCardTransactionType;
use Modules\GiftCard\Services\GiftCard\GiftCardServiceInterface;
use Modules\GiftCard\Services\GiftCardTransaction\GiftCardTransactionServiceInterface;
use Modules\Order\Enums\OrderPaymentStatus;
use Modules\Order\Enums\OrderProductStatus;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\OrderType;
use Modules\Order\Events\OrderMergeBillingPaid;
use Modules\Order\Events\OrderUpdateStatus;
use Modules\Order\Models\Order;
use Modules\Order\Services\Order\OrderServiceInterface;
use Modules\Payment\Enums\PaymentMethod;
use Modules\Payment\Enums\PaymentMode;
use Modules\Pos\Models\PosSession;
use Modules\SeatingPlan\Enums\TableStatus;
use Modules\SeatingPlan\Models\Table;
use Modules\SeatingPlan\Models\TableMerge;
use Modules\Support\Money;
use Throwable;


class OrderPaymentService implements OrderPaymentServiceInterface
{
    /** @inheritDoc */
    public function storePayment(int|string $id, array $data): void
    {
        // Outer transaction + lockForUpdate sobre la Order: evita que dos
        // pagos concurrentes lean la misma due_amount y ambos pasen la
        // validacion de overpayment. El lock se mantiene hasta el commit final.
        DB::transaction(function () use ($id, $data) {
            $order = app(OrderServiceInterface::class)->getModel()
                ->query()
                ->lockForUpdate()
                ->findOrFail($id);

            if (!is_null($order->table_merge_id)) {
                $orders = app(OrderServiceInterface::class)
                    ->getModel()
                    ->query()
                    ->activeOrders()
                    ->with("table")
                    ->where("table_merge_id", $order->table_merge_id)
                    ->lockForUpdate()
                    ->get();

                $this->storePaymentMerge($orders, $data);
                return;
            }

            if ($order->table_id) {
                abort_if($order->next_status != OrderStatus::Completed || !$order->allowAddPayment(), 400, __("order::messages.order_payment_not_allowed"));
            } else {
                abort_unless($order->allowAddPayment(), 400, __("order::messages.order_payment_not_allowed"));
            }

            $user = auth()->user();

            $scale = Currency::subunit($order->currency);
            $factor = 10 ** $scale;

            $dueRounded = round($order->due_amount->amount(), $scale);
            $dueMinor = (int)round($dueRounded * $factor);

            $paymentMinorTotal = 0;
            foreach ($data['payments'] as $p) {
                $paymentMinorTotal += (int)round(($p['amount'] ?? 0) * $factor);
            }

            $dueAmount = $dueMinor / $factor;
            $amountToBePaid = $paymentMinorTotal / $factor;

            abort_if(
                $amountToBePaid > $dueAmount,
                400,
                __("order::messages.payment_overpaid", [
                    "paid" => number_format($amountToBePaid, $scale),
                    "total" => number_format($dueAmount, $scale)
                ])
            );

            abort_if(
                $data['payment_mode'] == PaymentMode::Full->value && $amountToBePaid < $dueAmount,
                400,
                __("order::messages.insufficient_payment", [
                    "paid" => number_format($amountToBePaid, $scale),
                    "total" => number_format($dueAmount, $scale)
                ])
            );

            $isOrderComplete = ($paymentMinorTotal === $dueMinor) && $order->next_status == OrderStatus::Completed;

            $session = PosSession::query()->findOrFail($data['session_id']);

            $payments = [];
            $giftCardRedemptions = [];
            foreach ($data['payments'] as $payment) {
                $minor = (int)round($payment['amount'] * $factor);
                $meta = null;

                if ($payment['method'] === PaymentMethod::GiftCard->value) {
                    $giftCard = app(GiftCardServiceInterface::class)->findOrFail($payment['gift_card_code']);
                    $giftCardAmount = app(GiftCardServiceInterface::class)->convertOrderAmountToGiftCardAmount(
                        giftCard: $giftCard,
                        amount: (float)$payment['amount'],
                        orderCurrency: $order->currency,
                        orderCurrencyRate: $payment['currency_rate'] ?? $order->currency_rate,
                    );
                    $meta = [
                        'gift_card_code' => $giftCard->code,
                        'gift_card_id' => $giftCard->id,
                    ];
                    $giftCardRedemptions[] = [
                        'gift_card' => $giftCard,
                        'amount' => $giftCardAmount->amount(),
                        'order_amount' => (float)$payment['amount'],
                    ];
                }

                $payments[] = [
                    "cashier_id" => $user->id,
                    "method" => $payment['method'],
                    "amount" => $minor / $factor,
                    "transaction_id" => $payment['transaction_id'] ?? null,
                    "session" => $session,
                    "meta" => $meta,
                ];
            }

            $order->storePayments($payments);
            $this->storeGiftCardRedemptions($order, $giftCardRedemptions);
            $updateData = [
                "closed_at" => $isOrderComplete && $order->type === OrderType::DineIn ? now() : null,
                "cashier_id" => $user->id,
                "pos_session_id" => $order->pos_session_id ?: $session->id
            ];

            $newStatus = null;
            if ($isOrderComplete) {
                $newStatus = OrderStatus::Completed;
            } else if ($order->status == OrderStatus::Pending && $order->isScheduledForToday()) {
                $newStatus = OrderStatus::Confirmed;
            }

            if (!is_null($newStatus)) {
                $updateData['status'] = $newStatus;
            }

            $order->update($updateData);

            if (!is_null($newStatus)) {
                event(
                    new OrderUpdateStatus(
                        order: $order,
                        status: $newStatus,
                        changedById: $user->id,
                    )
                );
            }
        });
    }

    /**
     * Store payment for merge orders
     *
     * @param Collection $orders
     * @param array $data
     * @return void
     * @throws Throwable
     */
    protected function storePaymentMerge(Collection $orders, array $data): void
    {
        abort_if($orders->count() == 0, 400, __("order::messages.order_payment_not_allowed"));

        $user = auth()->user();

        $scale = Currency::subunit($orders[0]->currency);
        $factor = 10 ** $scale;

        $orderDueMinor = [];
        $totalDueMinor = 0;

        /** @var Order $order */
        foreach ($orders as $order) {
            abort_if($order->next_status != OrderStatus::Completed || !$order->allowAddPayment(), 400, __("order::messages.order_payment_not_allowed"));
            $dueRounded = $order->due_amount->round($scale)->amount();
            $minor = (int)round($dueRounded * $factor);
            $orderDueMinor[$order->id] = $minor;
            $totalDueMinor += $minor;
        }

        abort_if($totalDueMinor <= 0, 400, __("order::messages.order_payment_not_allowed"));

        $amountToBePaid = 0;
        foreach ($data['payments'] as $p) {
            $amountToBePaid += ($p['amount'] ?? 0);
        }
        $amountToBePaid = round($amountToBePaid, $scale);

        $dueAmount = $totalDueMinor / $factor;

        abort_if(
            $amountToBePaid > $dueAmount,
            400,
            __("order::messages.payment_overpaid", [
                "paid" => number_format($amountToBePaid, 2),
                "total" => number_format($dueAmount, 2)
            ])
        );

        abort_if(
            $data['payment_mode'] == PaymentMode::Full->value && $amountToBePaid < $dueAmount,
            400,
            __("order::messages.insufficient_payment", [
                "paid" => number_format($amountToBePaid, 2),
                "total" => number_format($dueAmount, 2)
            ])
        );

        $session = PosSession::query()->findOrFail($data['session_id']);

        $ordersIds = $orders->pluck('id')->values()->all();
        $lastOrderId = end($ordersIds);

        $perOrderPayments = [];
        $orderPaidMinor = [];

        foreach ($orders as $order) {
            $perOrderPayments[$order->id] = [];
            $orderPaidMinor[$order->id] = 0;
        }

        foreach ($data['payments'] as $payment) {
            $paymentMinor = (int)round($payment['amount'] * $factor);
            $distributedMinor = 0;

            foreach ($orders as $order) {
                if ($order->id === $lastOrderId) {
                    $share = $paymentMinor - $distributedMinor;
                } else {
                    $share = intdiv($paymentMinor * $orderDueMinor[$order->id], $totalDueMinor);
                    $distributedMinor += $share;
                }

                if ($share < 0) {
                    $share = 0;
                }

                $amount = $share / $factor;

                $perOrderPayments[$order->id][] = [
                    "cashier_id" => $user->id,
                    "method" => $payment['method'],
                    "amount" => $amount,
                    "session" => $session,
                    "transaction_id" => $payment['transaction_id'] ?? null,
                    "meta" => $payment['method'] === PaymentMethod::GiftCard->value
                        ? ['gift_card_code' => $payment['gift_card_code'] ?? null]
                        : null,
                ];

                $orderPaidMinor[$order->id] += $share;
            }
        }

        $merge = TableMerge::query()->findOrFail($orders->first()->table_merge_id);

        DB::transaction(function () use ($orders, $user, $merge, $data, $session, $perOrderPayments, $orderPaidMinor, $orderDueMinor) {
            /** @var Order $order */
            foreach ($orders as $order) {
                $dueMinor = $orderDueMinor[$order->id];
                $paidMinor = $orderPaidMinor[$order->id];

                $isOrderComplete = ($dueMinor === $paidMinor) && $order->next_status == OrderStatus::Completed;

                $order->storePayments($perOrderPayments[$order->id]);
                $this->storeGiftCardRedemptionsForPayments($order, $perOrderPayments[$order->id]);

                $order->update([
                    "status" => $isOrderComplete ? OrderStatus::Completed : $order->status,
                    "closed_at" => $isOrderComplete ? now() : null,
                    "cashier_id" => $user->id,
                    "pos_session_id" => $order->pos_session_id ?: $session->id
                ]);

                if ($isOrderComplete) {
                    event(
                        new OrderUpdateStatus(
                            order: $order,
                            status: OrderStatus::Completed,
                            changedById: $user->id,
                        )
                    );
                }
            }

            if ($orders->filter(fn($order) => $order->status != OrderStatus::Completed)->isEmpty()) {
                $query = Table::query()->where('current_merge_id', $merge->id);
                $tables = $query->get();
                $query->update(["current_merge_id" => null, "status" => TableStatus::Cleaning]);
                $tables->each(fn(Table $table) => $table->storeStatusLog(status: TableStatus::Cleaning));
                $merge->update(["closed_at" => now(), "closed_by" => $user->id]);
                event(new OrderMergeBillingPaid($merge));
            }

            $this->dispatchPrint($order, $data);
        });
    }

    /**
     * @throws Throwable
     */
    protected function storeGiftCardRedemptionsForPayments(Order $order, array $payments): void
    {
        foreach ($payments as $payment) {
            if (($payment['method'] ?? null) !== PaymentMethod::GiftCard->value || empty($payment['meta']['gift_card_code'])) {
                continue;
            }

            $giftCard = app(GiftCardServiceInterface::class)->findOrFail($payment['meta']['gift_card_code']);
            $giftCardAmount = app(GiftCardServiceInterface::class)->convertOrderAmountToGiftCardAmount(
                giftCard: $giftCard,
                amount: (float)$payment['amount'],
                orderCurrency: $order->currency,
                orderCurrencyRate: $payment['currency_rate'] ?? $order->currency_rate,
            );

            app(GiftCardTransactionServiceInterface::class)->record($giftCard, [
                'type' => GiftCardTransactionType::Redeem,
                'amount' => $giftCardAmount->amount(),
                'amount_in_order_currency' => (float)$payment['amount'],
                'order_currency' => $order->currency,
                'exchange_rate' => $order->currency_rate,
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'notes' => __("order::messages.order_paid_successfully"),
            ]);
        }
    }

    /**
     * @throws Throwable
     */
    protected function storeGiftCardRedemptions(Order $order, array $giftCardRedemptions): void
    {
        foreach ($giftCardRedemptions as $redemption) {
            app(GiftCardTransactionServiceInterface::class)
                ->record($redemption['gift_card'],
                    [
                        'type' => GiftCardTransactionType::Redeem,
                        'amount' => $redemption['amount'],
                        'amount_in_order_currency' => $redemption['order_amount'],
                        'order_currency' => $order->currency,
                        'exchange_rate' => $redemption['order_amount'] > 0 ? round($redemption['amount'] / $redemption['order_amount'], 8) : null,
                        'order_id' => $order->id,
                        'branch_id' => $order->branch_id,
                        'notes' => __("order::messages.order_paid_successfully"),
                    ]
                );
        }
    }

    /** @inheritDoc */
    public function getPaymentMeta(int|string $id = null): array
    {
        $query = app(OrderServiceInterface::class)->getModel()
            ->query()
            ->select("id", "branch_id", "table_merge_id", "subtotal", "total", "currency", "due_amount")
            ->withCount(["products" => fn($q) => $q->whereNotIn("status", [OrderProductStatus::Cancelled, OrderProductStatus::Refunded])])
            ->whereNot("payment_status", OrderPaymentStatus::Paid)
            ->activeOrders()
            ->with(["branch", "taxes", "discount"]);

        /** @var Order $order */
        $order = (clone $query)->where(fn($query) => $query->where('id', $id)
            ->orWhere('reference_no', $id))
            ->firstOrFail();

        $orders = collect();
        if (!is_null($order->table_merge_id)) {
            $orders = (clone $query)
                ->where("table_merge_id", $order->table_merge_id)
                ->where("id", "!=", $order->id)
                ->get();
        }

        $orders->push($order);

        $productsCount = 0;
        $subtotal = new Money(0, $order->currency);
        $grandTotal = new Money(0, $order->currency);
        $totalTax = new Money(0, $order->currency);
        $totalDiscount = new Money(0, $order->currency);
        $dueAmount = new Money(0, $order->currency);
        $totalPaid = new Money(0, $order->currency);

        /** @var Order $orderRow */
        foreach ($orders as $orderRow) {

            $productsCount += $orderRow->products_count;

            $subtotal = $subtotal->add($orderRow->subtotal);
            $grandTotal = $grandTotal->add($orderRow->total);
            $totalTax = $totalTax->add($orderRow->totalTax());

            if (!is_null($orderRow->discount)) {
                $totalDiscount = $totalDiscount->add($orderRow->discount->amount);
            }

            $dueAmount = $dueAmount->add($orderRow->due_amount);

            $totalPaid = $totalPaid->add($orderRow->total->subtract($orderRow->due_amount));
        }

        return [
            "payment_methods" => array_filter(
                PaymentMethod::toArrayTrans(),
                fn($orderType) => in_array($orderType['id'], $order->branch->payment_methods ?: [])
            ),
            "payment_modes" => PaymentMode::toArrayTrans(),
            "quick_pay_amounts" => $this->resolveQuickPayAmounts($order->branch->quick_pay_amounts),
            "order" => [
                "total_products" => $productsCount,
                "sub_total" => $subtotal->round(),
                "grand_total" => $grandTotal->round(),
                "total_tax" => $totalTax->round(),
                "discount" => $totalDiscount->round(),
                "due_amount" => $dueAmount->round(),
                "total_paid" => $totalPaid->round(),
                "currency" => $order->currency,
                "precision" => Currency::subunit($order->currency)
            ]
        ];
    }

    /**
     * Resolve quick pay button values, with a fallback default and max 6 values.
     *
     * @param array|null $values
     * @return array
     */
    private function resolveQuickPayAmounts(?array $values): array
    {
        $amounts = collect($values ?? [])
            ->map(fn($value) => is_numeric($value) ? (float)$value : null)
            ->filter(fn($value) => !is_null($value) && $value > 0)
            ->unique()
            ->take(6)
            ->values()
            ->all();

        if (count($amounts) === 0) {
            return [10, 15, 20, 30, 50, 100];
        }

        return $amounts;
    }
}
