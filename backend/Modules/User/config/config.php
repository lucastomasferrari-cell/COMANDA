<?php

use Modules\User\Enums\{PermissionAction as Action};

return [
    'permissions' => [
        "users" => [Action::Index, Action::Show, Action::Create, Action::Edit, Action::Destroy],
        "customers" => [Action::Index, Action::Show, Action::Create, Action::Edit, Action::Destroy],
        "roles" => [Action::Index, Action::Show, Action::Create, Action::Edit, Action::Destroy],
        "profiles" => [Action::Edit]
    ],
];
