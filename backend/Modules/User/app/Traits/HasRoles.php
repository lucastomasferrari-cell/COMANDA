<?php

namespace Modules\User\Traits;

use Modules\User\Enums\DefaultRole;

trait HasRoles
{
    use \Spatie\Permission\Traits\HasRoles;

    /**
     * Determine if the current user has 'Super admin'
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(DefaultRole::SuperAdmin->value);
    }

    /**
     * Get roles with permissions
     *
     * @return array
     */
    public function getRolesWithPermissions(): array
    {
        return $this->roles()->with('permissions')
            ->get()
            ->map(fn($role) => [
                "name" => $role->name,
                "display_name" => $role->display_name,
                "permissions" => $role->permissions->pluck('name')->toArray(),
            ])
            ->toArray();
    }
}
