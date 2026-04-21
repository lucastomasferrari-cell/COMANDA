<?php

use Modules\User\Enums\PermissionAction as Action;

return [
    'permissions' => [
        "gift_cards" => [Action::Index, Action::Show, Action::Create, Action::Edit, Action::Destroy, Action::Analytics],
        "gift_card_transactions" => [Action::Index, Action::Show],
        "gift_card_batches" => [Action::Index, Action::Show, Action::Create, Action::Destroy],
    ],
];
