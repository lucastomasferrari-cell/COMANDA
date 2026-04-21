<?php

use Modules\User\Enums\{PermissionAction as Action};

return [
    'permissions' => [
        "orders" => [
            Action::Index,
            Action::Show,
            Action::Create,
            Action::Edit,
            Action::Cancel,
            Action::Refund,
            Action::Active,
            Action::Upcoming,
            Action::ReceivePayment,
            Action::UpdateStatus,
            Action::Financials,
            Action::Print,
        ],
        "reasons" => [Action::Index, Action::Show, Action::Create, Action::Edit, Action::Destroy]
    ],
];
