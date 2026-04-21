<?php

namespace Modules\User\Enums;

use Modules\Support\Traits\EnumArrayable;
use Modules\Support\Traits\EnumTranslatable;

enum DefaultRole: string
{
    use EnumArrayable, EnumTranslatable;

    case SuperAdmin = "super_admin";
    case Admin = "admin";
    case AdminBranch = "admin_branch";
    case Manager = "manager";
    case Cashier = "cashier";
    case Kitchen = "kitchen";
    case Waiter = "waiter";
    case Customer = "customer";
    case HrManager = "hr_manager";
    case HrOfficer = "hr_officer";

    /**
     * Get branch available roles
     *
     * @return array
     */
    public static function getBranchAvailableRoles(): array
    {
        return [
            DefaultRole::AdminBranch->value,
            DefaultRole::Manager->value,
            DefaultRole::Waiter->value,
            DefaultRole::Cashier->value,
            DefaultRole::Kitchen->value,
        ];
    }

    /** @inheritDoc */
    public static function getTransKey(): string
    {
        return "user::enums.default_roles";
    }

    /**
     * Get role permissions
     *
     * @return array|string
     */
    public function getPermissions(): array|string
    {
        $permissions = require __DIR__ . "/../../Resources/roles_permissions.php";

        return $permissions[$this->value] ?? [];
    }
}
