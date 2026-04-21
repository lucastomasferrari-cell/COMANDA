<?php

namespace Modules\User\Services\Account;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\User\Models\User;

class AccountService implements AccountServiceInterface
{
    /** @inheritDoc */
    public function me(): User
    {
        return $this->getModel()
            ->with(["branch"])
            ->where('id', auth()->id())
            ->first();
    }

    /** @inheritDoc */
    public function getModel(): User
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return User::class;
    }

    /** @inheritDoc */
    public function updateProfile(array $data): User
    {
        auth()->user()->update($data);
        
        return auth()->user()->fresh();
    }

    /** @inheritDoc */
    public function updatePassword(array $data): bool
    {
        $user = auth()->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('user::messages.current_password_incorrect'),
            ]);
        }

        if (Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('user::messages.new_password_same_current_password'),
            ]);
        }

        $isUpdated = $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        if ($isUpdated && isset($data['logout_from_other_devices']) && $data['logout_from_other_devices']) {
            $this->logoutFromOtherDevices($user);
        }

        return $isUpdated;
    }

    /** @inheritDoc */
    public function logoutFromOtherDevices(User $user): void
    {
        $user->tokens()->where('id', '!=', optional($user->currentAccessToken())->id)->delete();
    }
}
