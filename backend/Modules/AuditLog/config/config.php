<?php

use Modules\User\Enums\PermissionAction as Action;

return [
    'permissions' => [
        "audit_logs" => [Action::Index, Action::Show],
    ],
];
