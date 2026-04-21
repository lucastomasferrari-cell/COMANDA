<?php

namespace Modules\Setting\Enums;

use Modules\Support\Traits\EnumArrayable;

enum SettingSection: string
{
    use EnumArrayable;

    case General = "general";
    case Application = "application";
    case Currency = "currency";
    case Mail = "mail";
    case Filesystem = "filesystem";
    case Logo = "logo";
    case Appearance = "appearance";
    case Kitchen = "kitchen";
    case Pwa = "pwa";

}
