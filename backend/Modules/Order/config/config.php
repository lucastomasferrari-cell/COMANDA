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
            Action::Reopen,
            Action::VoidItemBeforeFire,
            Action::VoidItemAfterFire,
            Action::VoidOrderBeforePayment,
            Action::DiscountSmall,
            Action::DiscountMedium,
            Action::DiscountLarge,
            Action::ModifyPrice,
            Action::CustomItemOverLimit,
        ],
        "reasons" => [Action::Index, Action::Show, Action::Create, Action::Edit, Action::Destroy]
    ],
];
