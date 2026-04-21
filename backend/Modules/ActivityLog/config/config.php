<?php

use Modules\User\Enums\{PermissionAction as Action};

return [
    'permissions' => [
        "activity_logs" => [Action::Index, Action::Show],
        "authentication_logs" => [Action::Index]
    ],
];
