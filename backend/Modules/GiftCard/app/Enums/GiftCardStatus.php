<?php

namespace Modules\GiftCard\Enums;

use Modules\Support\Traits\EnumArrayable;
use Modules\Support\Traits\EnumTranslatable;

enum GiftCardStatus: string
{
    use EnumArrayable, EnumTranslatable;

    case Active = 'active';
    case Used = 'used';
    case Expired = 'expired';
    case Disabled = 'disabled';

    public static function getTransKey(): string
    {
        return 'giftcard::enums.statuses';
    }
}
