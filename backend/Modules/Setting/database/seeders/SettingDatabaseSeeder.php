<?php

namespace Modules\Setting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Setting\Models\Setting;

class SettingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::setMany([
            "supported_countries" => ['JO'],
            'default_country' => 'JO',
            'supported_currencies' => ["JOD"],
            'default_currency' => 'JOD',
            'supported_locales' => ['en', 'ar'],
            'default_locale' => 'en',
            'default_timezone' => 'Asia/Amman',
            'translatable' => [
                'app_name' => 'Forkiva',
            ],
            "encryptable" => [
            ],
            'default_date_format' => 'Y-m-d',
            'default_time_format' => 'h:i A',
            'start_of_week' => "sunday",
            'end_of_week' => "saturday",
            'default_filesystem_disk' => 'public',
            'default_private_filesystem_disk' => 'local',
            'default_theme_mode' => 'light',
            'theme_primary_color' => '#F57C00',
            'theme_secondary_color' => '#043A63',
            'theme_success_color' => '#2ecc71',
            'theme_info_color' => '#03C3EC',
            'theme_warning_color' => '#f1c40f',
            'theme_error_color' => '#e74c3c',
            'pwa_enabled' => false,
            'pwa_name' => 'Forkiva',
            'pwa_short_name' => 'Forkiva',
            'pwa_icon' => null,
            'pwa_background_color' => '#FAF8F3',
            'pwa_theme_color' => '#FAF8F3',
            'pwa_description' => 'Forkiva restaurant POS and management system.',
        ]);
    }
}
