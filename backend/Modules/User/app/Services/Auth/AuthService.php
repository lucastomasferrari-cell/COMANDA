<?php

namespace Modules\User\Services\Auth;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Modules\User\Models\PersonalAccessToken;
use Modules\User\Models\User;

class AuthService implements AuthServiceInterface
{
    /** @inheritDoc */
    public function login(array $credentials): array
    {
        $isEmail = $this->isEmail($credentials['identifier']);

        $user = User::when($isEmail, fn(Builder $query) => $query->where("email", $credentials["identifier"]))
            ->when(!$isEmail, fn(Builder $query) => $query->where("username", $credentials["identifier"]))
            ->withTrashed()
            ->withoutGlobalActive()
            ->first();
        $this->validateUserStatus($user);

        abort_if(!Hash::check($credentials['password'], $user->password), 401, __('auth.failed'));

        return $this->grantToken($user);
    }

    /** @inheritDoc */
    public function isEmail(string $identifier): bool
    {
        return filter_var($identifier, FILTER_VALIDATE_EMAIL);
    }

    /** @inheritDoc */
    public function validateUserStatus(?User $user): void
    {
        abort_if(!$user, 401, __('auth.failed'));
        abort_if($user->trashed(), 401, __('user::messages.account_deleted'));
        abort_if(!$user->is_active, 401, __('user::messages.account_not_activated'));
    }

    /** @inheritDoc */
    public function grantToken(User $user, bool $remember = false, string $name = 'Login'): array
    {
        $token = $user->createToken($name)->plainTextToken;

        event(new Login("api", $user, $remember));

        return ['user' => $user, 'token' => $token];
    }

    /** @inheritDoc */
    public function logout(): bool
    {
        $user = auth()->user();

        /** @var PersonalAccessToken $currentAccessToken */
        $currentAccessToken = $user->currentAccessToken();
        $logout = $currentAccessToken->delete();

        event(new Logout("api", $user));

        return $logout;
    }
}
