<?php

namespace Modules\SeatingPlan\Enums;

use Modules\Support\Traits\EnumArrayable;
use Modules\Support\Traits\EnumTranslatable;

enum TableShape: string
{
    use EnumArrayable, EnumTranslatable;

    case Circle = "circle";
    case Rectangle = "rectangle";
    case Square = "square";

    /** @inheritDoc */
    public static function getTransKey(): string
    {
        return "seatingplan::enums.table_shapes";
    }
}
