<?php

namespace Modules\Order\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Core\Http\Requests\Request;
use Modules\Currency\Currency;
use Modules\GiftCard\Services\GiftCard\GiftCardServiceInterface;
use Modules\Order\Models\Order;
use Modules\Order\Services\Order\OrderServiceInterface;
use Modules\Payment\Enums\PaymentMethod;
use Modules\Payment\Enums\PaymentMode;
use Modules\Pos\Enums\PosSessionStatus;
use Throwable;

class OrderPaymentRequest extends Request
{
    protected ?Order $order = null;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $branch = $this->getOrder()->branch;

        $rules = [
            "payments" => 'required|array',
            "payments.*.method" => ["required", Rule::in($branch->payment_methods ?: [])],
            "payments.*.amount" => "required|numeric|min:0.0001|max:99999999999999",
            "payments.*.transaction_id" => "nullable|string|max:255",
            "payments.*.gift_card_code" => "nullable|string|max:255",
            "payment_mode" => ["required", Rule::enum(PaymentMode::class)],
            "with_print" => "required|boolean",
            "register_id" => [
                "bail",
                "required",
                "integer",
                Rule::exists("pos_registers", "id")
                    ->whereNull("deleted_at")
                    ->where('is_active', true)
                    ->where('branch_id', $branch->id)
            ],
            "session_id" => [
                "bail",
                "required",
                "integer",
                Rule::exists("pos_sessions", "id")
                    ->whereNull("deleted_at")
                    ->where('status', PosSessionStatus::Open->value)
                    ->where('branch_id', $branch->id)
                    ->where('pos_register_id', $this->input('register_id'))
            ],
        ];

        $totalCashPayment = 0;
        foreach ($this->input('payments', []) as $payment) {
            if (isset($payment['method']) && $payment['method'] === PaymentMethod::Cash->value) {
                $totalCashPayment += $payment['amount'];
            }

            if (isset($payment['method']) && $payment['method'] === PaymentMethod::GiftCard->value) {
                $rules["payments.*.gift_card_code"] = "required|string|max:255";
            }
        }

        if ($totalCashPayment > 0) {
            $rules["customer_given_amount"] = "required|numeric|min:0.0001|max:99999999999999";
            $rules["change_return"] = "required|numeric|min:" . round($this->input('customer_given_amount', Currency::subunit($branch->currency)) - $totalCashPayment, 3) . "|max:99999999999999";
        }

        return $rules;
    }

    /**
     * Resolve the target order once per request lifecycle.
     *
     * @return Order
     */
    protected function getOrder(): Order
    {
        if (!$this->order instanceof Order) {
            $this->order = app(OrderServiceInterface::class)
                ->findOrFail($this->route('orderId'), true);
        }

        return $this->order;
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateGiftCardPayments($validator);
        });
    }

    /**
     * Validate gift card payments for existence, state, and available balance.
     *
     * @param Validator $validator
     * @return void
     */
    protected function validateGiftCardPayments(Validator $validator): void
    {
        $order = $this->getOrder();
        $giftCards = [];
        $requestedAmounts = [];
        $giftCardIndexes = [];

        foreach ($this->input('payments', []) as $index => $payment) {
            if (($payment['method'] ?? null) !== PaymentMethod::GiftCard->value) {
                continue;
            }

            $giftCardCode = (string)($payment['gift_card_code'] ?? '');
            if (blank($giftCardCode)) {
                continue;
            }

            try {
                $giftCard = app(GiftCardServiceInterface::class)->findOrFail($giftCardCode);
            } catch (Throwable) {
                $validator->errors()->add("payments.$index.gift_card_code", __('giftcard::messages.invalid_card'));
                continue;
            }

            if ($giftCard->isExpired()) {
                $validator->errors()->add("payments.$index.gift_card_code", __('giftcard::messages.card_expired'));
                continue;
            }

            if ($giftCard->isDisabled()) {
                $validator->errors()->add("payments.$index.gift_card_code", __('giftcard::messages.card_disabled'));
                continue;
            }

            if ($giftCard->customer_id && $giftCard->customer_id !== $order->customer_id) {
                $validator->errors()->add("payments.$index.gift_card_code", __('giftcard::messages.card_customer_mismatch'));
                continue;
            }

            $giftCards[$giftCard->code] = $giftCard;
            $giftCardIndexes[$giftCard->code][] = $index;
            $requestedAmounts[$giftCard->code] = ($requestedAmounts[$giftCard->code] ?? 0)
                + app(GiftCardServiceInterface::class)->convertOrderAmountToGiftCardAmount(
                    giftCard: $giftCard,
                    amount: (float)($payment['amount'] ?? 0),
                    orderCurrency: $order->currency,
                    orderCurrencyRate: $order->currency_rate,
                )->amount();
        }

        foreach ($requestedAmounts as $giftCardCode => $requestedAmount) {
            $giftCard = $giftCards[$giftCardCode];

            if ($requestedAmount > $giftCard->current_balance->amount()) {
                foreach ($giftCardIndexes[$giftCardCode] as $index) {
                    $validator->errors()->add("payments.$index.gift_card_code", __('giftcard::messages.insufficient_balance'));
                }
            }
        }
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "order::attributes.payments";
    }
}
