<?php

use Modules\User\Enums\{PermissionAction as Action};

return [
    'permissions' => [
        "pos" => [Action::Index, Action::KitchenViewer],
        "pos_registers" => [Action::Index, Action::Show, Action::Create, Action::Edit, Action::Destroy],
        "pos_sessions" => [Action::Index, Action::Show, Action::Open, Action::Close],
        "pos_cash_movements" => [Action::Index, Action::Show, Action::Create]
    ],
];
