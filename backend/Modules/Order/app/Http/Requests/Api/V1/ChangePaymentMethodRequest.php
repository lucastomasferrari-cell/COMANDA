<?php

namespace Modules\Order\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\Payment\Enums\PaymentMethod;

class ChangePaymentMethodRequest extends Request
{
    public function rules(): array
    {
        return [
            'payment_id' => 'required|integer|exists:payments,id',
            'new_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reason' => 'required|string|min:20|max:2000',
            'manager_approval_token' => 'required|string',
        ];
    }

    protected function availableAttributes(): string
    {
        return "order::attributes.orders";
    }
}
