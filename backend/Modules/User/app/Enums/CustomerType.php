<?php

namespace Modules\User\Enums;

use Modules\Support\Traits\EnumArrayable;
use Modules\Support\Traits\EnumTranslatable;

enum CustomerType: string
{
    use EnumArrayable, EnumTranslatable;

    case Regular = 'regular';
    case Vip = 'vip';
    case Corporate = 'corporate';

    /** @inheritDoc */
    public static function getTransKey(): string
    {
        return 'user::enums.customer_types';
    }

    /**
     * Determine customer is regular
     *
     * @return bool
     */
    public function isRegular(): bool
    {
        return $this == CustomerType::Regular;
    }

    /**
     * Determine customer is corporate
     *
     * @return bool
     */
    public function isCorporate(): bool
    {
        return $this == CustomerType::Corporate;
    }

    /**
     * Determine customer is vip
     *
     * @return bool
     */
    public function isVip(): bool
    {
        return $this == CustomerType::Vip;
    }
}
