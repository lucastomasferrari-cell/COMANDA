<?php

namespace Modules\Payment\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\Payment\Enums\PaymentMethod;

class SavePaymentMethodRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::enum(PaymentMethod::class)],
            'impacts_cash' => 'required|boolean',
            'is_active' => 'required|boolean',
            'order' => 'nullable|integer|min:0',
        ];
    }

    protected function availableAttributes(): string
    {
        return 'payment::attributes.payment_methods';
    }
}
