<?php

use Modules\User\Enums\PermissionAction as Action;

return [
    'permissions' => [
        'database_tools' => [
            Action::Index,
            Action::Backup,
            Action::Download,
            Action::Restore,
        ],
    ],
];
