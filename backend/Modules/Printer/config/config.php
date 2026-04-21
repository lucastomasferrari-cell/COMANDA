<?php

use Modules\Printer\Enum\PrinterPaperSize;
use Modules\User\Enums\{PermissionAction as Action};

return [
    'permissions' => [
        "printers" => [Action::Index, Action::Show, Action::Create, Action::Edit, Action::Destroy],
        "print_agents" => [Action::Index, Action::Show, Action::Create, Action::Edit, Action::Destroy],
    ],

    'media_profiles' => [
        PrinterPaperSize::Paper58mm->value => [
            'paper_width_mm' => 58,
            'pixel_width' => 384,
        ],

        PrinterPaperSize::Paper80mm->value => [
            'paper_width_mm' => 80,
            'pixel_width' => 576,
        ],
    ],

    'fonts' => [
        'cairo' => [
            'family' => 'Cairo',
            'regular' => storage_path('fonts/Cairo-Regular.ttf'),
            'bold' => storage_path('fonts/Cairo-Bold.ttf'),
        ],
        'inter' => [
            'family' => 'Inter',
            'regular' => storage_path('fonts/Inter.ttf'),
            'bold' => storage_path('fonts/Inter.ttf'),
        ],
        'noto_sans_arabic' => [
            'family' => 'Noto Sans Arabic',
            'regular' => storage_path('fonts/NotoSansArabic.ttf'),
            'bold' => storage_path('fonts/NotoSansArabic.ttf'),
        ],
    ],
];
