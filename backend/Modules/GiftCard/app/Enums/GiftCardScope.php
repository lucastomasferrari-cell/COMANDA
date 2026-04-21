<?php

namespace Modules\GiftCard\Enums;

use Modules\Support\Traits\EnumArrayable;
use Modules\Support\Traits\EnumTranslatable;

enum GiftCardScope: string
{
    use EnumArrayable, EnumTranslatable;

    case Branch = 'branch';
    case Global = 'global';

    public static function getTransKey(): string
    {
        return 'giftcard::enums.scopes';
    }
}
