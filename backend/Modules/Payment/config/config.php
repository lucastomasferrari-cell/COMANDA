<?php

use Modules\User\Enums\{PermissionAction as Action};

return [
    'permissions' => [
        "payments" => [Action::Index, Action::Show]
    ],
];
