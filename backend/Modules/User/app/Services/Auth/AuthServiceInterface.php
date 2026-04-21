<?php

namespace Modules\User\Services\Auth;

use Modules\User\Models\User;

interface AuthServiceInterface
{
    /**
     * Login user
     *
     * @param array $credentials
     * @return array
     */
    public function login(array $credentials): array;

    /**
     * Logout
     *
     * @return bool
     */
    public function logout(): bool;

    /**
     * Check if the identifier is email value
     *
     * @param string $identifier
     * @return bool
     */
    public function isEmail(string $identifier): bool;

    /**
     * Validate the user status
     *
     * @param User|null $user
     * @return void
     */
    public function validateUserStatus(?User $user): void;

    /**
     * Grant user auth response
     *
     * @param User $user
     * @param bool $remember
     * @param string $name
     * @return array
     */
    public function grantToken(User $user, bool $remember = false, string $name = 'Login'): array;
}
