<?php

namespace Modules\Order\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Core\Http\Requests\Request;
use Modules\GiftCard\Services\GiftCard\GiftCardServiceInterface;
use Modules\Order\Models\Order;
use Modules\Order\Services\Order\OrderServiceInterface;
use Modules\Payment\Enums\RefundPaymentMethod;
use Throwable;

class CancelOrRefundOrderRequest extends Request
{
    protected ?Order $order = null;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $order = $this->getOrder();

        $rules = [
            "reason_id" => "bail|required|exists:reasons,id,deleted_at,NULL,is_active,1",
            "register_id" => [
                "bail",
                "required",
                "integer",
                Rule::exists("pos_registers", "id")
                    ->whereNull("deleted_at")
                    ->where('is_active', true)
                    ->where('branch_id', $order->branch->id)
            ],
            "note" => "nullable|string|max:1000",
        ];

        if ($order->hasRefundAmount()) {
            $rules["refund_payment_method"] = [
                "required",
                Rule::in(
                    array_filter(
                        RefundPaymentMethod::values(),
                        fn($value) => in_array($value, $order->branch->payment_methods ?: [])
                    )
                )
            ];
            $rules["gift_card_code"] = "nullable|string|max:255";

            if ($this->input('refund_payment_method') === RefundPaymentMethod::GiftCard->value) {
                $rules["gift_card_code"] = "required|string|max:255";
            }
        }

        return $rules;
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
            $this->validateRefundGiftCard($validator);
        });
    }

    /**
     * Validate the selected gift card when refunding to stored value.
     *
     * @param Validator $validator
     * @return void
     */
    protected function validateRefundGiftCard(Validator $validator): void
    {
        if ($this->input('refund_payment_method') !== RefundPaymentMethod::GiftCard->value) {
            return;
        }

        $giftCardCode = (string)$this->input('gift_card_code', '');
        if (blank($giftCardCode)) {
            return;
        }

        try {
            $giftCard = app(GiftCardServiceInterface::class)->findOrFail($giftCardCode);
        } catch (Throwable) {
            $validator->errors()->add('gift_card_code', __('giftcard::messages.invalid_card'));
            return;
        }

        if ($giftCard->isExpired()) {
            $validator->errors()->add('gift_card_code', __('giftcard::messages.card_expired'));
            return;
        }

        if ($giftCard->isDisabled()) {
            $validator->errors()->add('gift_card_code', __('giftcard::messages.card_disabled'));
        }
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

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "order::attributes.cancel_or_refund";
    }
}
