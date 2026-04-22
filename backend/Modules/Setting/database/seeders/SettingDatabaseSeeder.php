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
            "supported_countries" => ['AR'],
            'default_country' => 'AR',
            'supported_currencies' => ["ARS"],
            'default_currency' => 'ARS',
            'supported_locales' => ['es_AR', 'en'],
            'default_locale' => 'es_AR',
            'default_timezone' => 'America/Argentina/Buenos_Aires',
            'translatable' => [
                'app_name' => 'Comanda',
            ],
            "encryptable" => [
            ],
            'default_date_format' => 'd/m/Y',
            'default_time_format' => 'H:i',
            'start_of_week' => "monday",
            'end_of_week' => "sunday",
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
            'pwa_name' => 'Comanda',
            'pwa_short_name' => 'Comanda',
            'pwa_icon' => null,
            'pwa_background_color' => '#FAF8F3',
            'pwa_theme_color' => '#FAF8F3',
            'pwa_description' => 'Comanda — sistema POS y gestión para restaurantes.',
        ]);
    }
}
