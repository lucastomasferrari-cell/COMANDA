<?php

use Modules\User\Enums\{PermissionAction as Action};

return [
    'permissions' => [
        "floors" => [Action::Index, Action::Show, Action::Create, Action::Edit, Action::Destroy],
        "zones" => [Action::Index, Action::Show, Action::Create, Action::Edit, Action::Destroy],
        "tables" => [
            Action::Index,
            Action::Show,
            Action::Create,
            Action::Edit,
            Action::Destroy,
            Action::Viewer,
            Action::Merge,
            Action::Split,
            Action::UpdateStatus,
            Action::AssignWaiter,
            Action::FreeWithoutPayment,
        ],
        "table_merges" => [Action::Index, Action::Show],
    ],
];
