<?php

namespace Modules\User\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Modules\Core\Http\Requests\Request;
use Modules\User\Enums\DefaultRole;
use Modules\User\Enums\GenderType;
use Modules\User\Services\Role\RoleServiceInterface;
use Modules\User\Services\User\UserServiceInterface;

/**
 * @property int|null $role
 */
class SaveUserRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            ...$this->getBranchRule(true),
            "name" => "required|string|max:255",
            "username" => "bail|required|string|max:255|unique:users,username,{$this->route('id')}",
            "email" => "bail|required|email:rfc|max:50|unique:users,email,{$this->route('id')}",
            "password" => [
                ($this->method() == 'PUT' ? 'nullable' : 'required'),
                'string',
                Password::min(8)
                    ->max(20)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                "confirmed"
            ],
            "gender" => ["required", Rule::enum(GenderType::class)],
            'role' => [
                'bail',
                'required',
                'numeric',
                Rule::exists('roles', 'id')
                    ->whereNot('name', DefaultRole::Customer->value),
            ],
            "category_slugs" => "nullable|array",
            "category_slugs.*" => "bail|required|string|exists:categories,slug,deleted_at,NULL",
            "printer_id" => "bail|nullable|exists:printers,id",
            "is_active" => "required|boolean",
        ];

        if ($this->role && is_int($this->role)) {
            $role = app(RoleServiceInterface::class)->find($this->role);
            $branchRequired = !is_null($role) && in_array($role->name, DefaultRole::getBranchAvailableRoles());

            if ($branchRequired && $this->method() == 'POST') {
                $rules['branch_id'] = str_replace("nullable", "required", $rules['branch_id']);
            } else if ($branchRequired && $this->method() == 'PUT') {

                $user = app(UserServiceInterface::class)->findOrFail($this->route('id'));
                if (is_null($user->branch_id)) {
                    $rules = [
                        ...$this->getBranchRule(false, true),
                        ...$rules
                    ];
                }
            }
        }

        if (auth()->user()->assignedToBranch()) {
            $rules["role"] = [
                'bail',
                'required',
                'numeric',
                Rule::exists('roles', 'id')
                    ->whereIn('name', DefaultRole::getBranchAvailableRoles())
            ];
        }

        return $rules;
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "user::attributes.users";
    }
}
