<?php

namespace Modules\User\Http\Requests\Api\V1;

use Modules\Core\Http\Requests\Request;

class UpdateProfileRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = auth()->id();

        return [
            "name" => "required|string|max:255",
            "username" => "bail|required|string|max:255|unique:users,username,$userId",
            "email" => "bail|required|email:rfc|max:50|unique:users,email,$userId",
        ];
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "user::attributes.users";
    }
}
