<?php

namespace Modules\Support\app\Enums;

use Modules\Support\Traits\EnumArrayable;
use Modules\Support\Traits\EnumTranslatable;

enum MaritalStatus: string
{
    use EnumArrayable, EnumTranslatable;

    case Single = "single";
    case Married = "married";
    case Divorced = "divorced";
    case Widowed = "widowed";
    
    /** @inheritDoc */
    public static function getTransKey(): string
    {
        return "support::enums.marital_statuses";
    }
}
