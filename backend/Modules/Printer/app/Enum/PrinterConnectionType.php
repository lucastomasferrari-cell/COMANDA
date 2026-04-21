<?php

namespace Modules\Printer\Enum;

use Modules\Support\Traits\EnumArrayable;
use Modules\Support\Traits\EnumTranslatable;

enum PrinterConnectionType: string
{
    use EnumArrayable, EnumTranslatable;

    case Tcp = 'tcp';
    case Usb = 'usb';
    case Bluetooth = 'bluetooth';

    public static function getTransKey(): string
    {
        return "printer::enums.printer_connection_types";
    }
}
