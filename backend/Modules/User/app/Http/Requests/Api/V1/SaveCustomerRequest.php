<?php

namespace Modules\User\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Modules\Core\Http\Requests\Request;
use Modules\User\Enums\CustomerType;
use Modules\User\Enums\GenderType;

/**
 * @property int|null $role
 */
class SaveCustomerRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            "name" => "required|string|max:255",
            "username" => "bail|nullable|string|max:255|unique:users,username,{$this->route('id')}",
            "email" => "bail|nullable|email:rfc|max:50|unique:users,email,{$this->route('id')}",
            "password" => [
                'nullable',
                'string',
                Password::min(8)
                    ->max(20)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                "confirmed"
            ],
            "gender" => ["nullable", Rule::enum(GenderType::class)],
            "birthdate" => "nullable|date|before_or_equal:today",
            "note" => "nullable|string|max:2000",
            "customer_type" => ["required", Rule::enum(CustomerType::class)],
            "registration_number" => "nullable|string|max:120",
            "vat_tin" => "nullable|string|max:120",
            "profile_photo" => "nullable|file|image|mimes:jpeg,jpg,png|max:5120",
            "remove_profile_photo" => "nullable|boolean",
            "phone_country_iso_code" => "required|string|max:3",
            "phone" => [
                "bail",
                "required",
                "phone:phone_country_iso_code",
                "unique:users,phone,{$this->route('id')}"
            ],
            "is_active" => "required|boolean",
        ];
    }

    /** @inheritDoc */
    public function validationData(): array
    {
        $data = parent::validationData();
        $phone = $this->input('phone');
        $phoneCountryIsoCode = $this->input('phone_country_iso_code');

        if (!is_null($phone) && !is_null($phoneCountryIsoCode)) {
            $data['phone'] = phone($phone, $phoneCountryIsoCode);
        }

        return $data;
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "user::attributes.users";
    }
}
