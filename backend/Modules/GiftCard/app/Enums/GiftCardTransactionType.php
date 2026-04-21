<?php

namespace Modules\GiftCard\Enums;

use Modules\Support\Traits\EnumArrayable;
use Modules\Support\Traits\EnumTranslatable;

enum GiftCardTransactionType: string
{
    use EnumArrayable, EnumTranslatable;

    case Purchase = 'purchase';
    case Redeem = 'redeem';
    case Refund = 'refund';
    case Recharge = 'recharge';
    case Adjustment = 'adjustment';
    case Expire = 'expire';

    public static function getTransKey(): string
    {
        return 'giftcard::enums.transaction_types';
    }

    public function affectsBalanceBy(): int
    {
        return match ($this) {
            self::Purchase, self::Refund, self::Recharge, self::Adjustment => 1,
            self::Redeem, self::Expire => -1,
        };
    }
}
