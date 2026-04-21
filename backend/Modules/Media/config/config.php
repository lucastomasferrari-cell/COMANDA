<?php


use Modules\User\Enums\{PermissionAction as Action};

return [
    'permissions' => [
        "media" => [Action::Index, Action::Create, Action::Edit, Action::Destroy],
    ],
];
