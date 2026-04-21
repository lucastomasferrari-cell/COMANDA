<?php

namespace Modules\Branch\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\Currency\Currency;
use Modules\Order\Enums\OrderType;
use Modules\Payment\Enums\PaymentMethod;
use Modules\Support\Country;
use Modules\Support\TimeZone;


class SaveBranchRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            ...$this->getTranslationRules(["name" => "required|string|max:255"]),
            "legal_name" => "required|string|max:250",
            "vat_tin" => "required|string|max:100",
            "registration_number" => "required|string|max:100",
            "address_line1" => "required|string|max:255",
            "address_line2" => "nullable|string|max:255",
            "city" => "nullable|string|max:150",
            "state" => "nullable|string|max:150",
            "postal_code" => "nullable|string|max:50",
            "phone" => "required|string|max:20",
            "email" => "required|email:rfc|max:50",
            "country_code" => ["required", Rule::in(Country::codes())],
            "timezone" => ["required", Rule::in(TimeZone::keys())],
            "currency" => ["required", Rule::in(Currency::codes())],
            "latitude" => "nullable|numeric|between:-90,90",
            "longitude" => "nullable|numeric|between:-180,180",
            "is_active" => "required|boolean",
            "cash_difference_threshold" => "required|numeric|min:0|max:999999",
            'order_types' => "required|array|min:1",
            "order_types.*" => ["required", "distinct", Rule::in(OrderType::values())],
            'payment_methods' => "required|array|min:1",
            "payment_methods.*" => ["required", "distinct", Rule::in(PaymentMethod::values())],
            "quick_pay_amounts" => "nullable|array|max:6",
            "quick_pay_amounts.*" => "required|numeric|min:0.01|max:99999999999999|distinct",
        ];
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "branch::attributes.branches";
    }
}
