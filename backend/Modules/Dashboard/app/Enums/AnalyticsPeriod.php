<?php

namespace Modules\Dashboard\Enums;

use Modules\Support\Traits\EnumArrayable;
use Modules\Support\Traits\EnumTranslatable;

enum AnalyticsPeriod: string
{
    use EnumArrayable, EnumTranslatable;

    case AllTime = 'all_time';
    case Today = 'today';
    case ThisWeek = 'this_week';
    case ThisMonth = 'this_month';
    case ThisYear = 'this_year';

    /** @inheritDoc */
    public static function getTransKey(): string
    {
        return "dashboard::enums.analytics_periods";
    }
}
