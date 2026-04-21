<?php

namespace Modules\Setting\Services\Setting;

use App\Forkiva;
use Modules\Currency\Currency;
use Modules\Currency\Enums\ExchangeService;
use Modules\Media\Models\Media;
use Modules\Setting\Enums\AutoRefreshMode;
use Modules\Setting\Enums\SettingSection;
use Modules\Setting\Models\Setting;
use Modules\Setting\Repositories\SettingRepository;
use Modules\Support\Country;
use Modules\Support\DateFormats;
use Modules\Support\Enums\Day;
use Modules\Support\Enums\FilesystemDisk;
use Modules\Support\Enums\Frequency;
use Modules\Support\Enums\MailEncryptionProtocol;
use Modules\Support\Enums\Mailer;
use Modules\Support\Locale;
use Modules\Support\RTLDetector;
use Modules\Support\TimeFormats;
use Modules\Support\TimeZone;
use Storage;

class SettingService implements SettingServiceInterface
{
    /** @inheritDoc */
    public function label(): string
    {
        return __("setting::settings.settings.setting");
    }

    /** @inheritDoc */
    public function getModel(): Setting
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return Setting::class;
    }

    /** @inheritDoc */
    public function getAppSettings(bool $refresh = false): array
    {
        if ($refresh) {
            $this->refreshSettingBinding();
        }


        return [
            "supported_locales" => supportedLocaleKeys(),
            "supported_languages" => supportedLanguages(),
            "locale" => setting("default_locale"),
            "default_time_format" => setting('default_time_format', 'h:i A'),
            "start_of_week" => setting('start_of_week', Day::Sunday->value),
            "end_of_week" => setting('end_of_week', Day::Saturday->value),
            "fallback_locale" => fallbackLocale(),
            "is_rtl" => RTLDetector::detect(),
            "timezone" => setting("default_timezone"),
            "currency" => setting("default_currency"),
            "app_name" => setting("app_name"),
            "logo" => Media::getCacheMedia(setting('logo'))?->url ?: asset("logo.png"),
            "logo_data_base64" => getLogoBase64(),
            "favicon" => Media::getCacheMedia(setting('favicon'))?->url ?: asset("logo.png"),
            "default_theme_mode" => (string)setting('default_theme_mode', 'light'),
            "theme_primary_color" => (string)setting('theme_primary_color', '#F57C00'),
            "theme_secondary_color" => (string)setting('theme_secondary_color', '#043A63'),
            "theme_success_color" => (string)setting('theme_success_color', '#2ecc71'),
            "theme_info_color" => (string)setting('theme_info_color', '#03C3EC'),
            "theme_warning_color" => (string)setting('theme_warning_color', '#f1c40f'),
            "theme_error_color" => (string)setting('theme_error_color', '#e74c3c'),
            "pwa_enabled" => (bool)setting('pwa_enabled', false),
            "pwa_name" => (string)setting('pwa_name', setting('app_name', 'Comanda')),
            "pwa_short_name" => (string)setting('pwa_short_name', setting('app_name', 'Comanda')),
            "pwa_description" => (string)setting('pwa_description', 'Comanda — sistema POS y gestión para restaurantes.'),
            "pwa_background_color" => (string)setting('pwa_background_color', '#FAF8F3'),
            "pwa_theme_color" => (string)setting('pwa_theme_color', '#FAF8F3'),
            "pwa_icon" => Media::getCacheMedia(setting('pwa_icon'))?->url ?: (Media::getCacheMedia(setting('logo'))?->url ?: asset("logo.png")),
        ];
    }

    /** @inheritDoc */
    public function refreshSettingBinding(): void
    {
        app()->forgetInstance('setting');
        app()->singleton('setting', fn() => new SettingRepository(Setting::allCached()));
    }

    /** @inheritDoc */
    public function getAppSettingsVersion(): string
    {
        $snapshot = Setting::query()
            ->selectRaw('COUNT(*) as total_settings, MAX(updated_at) as latest_updated_at')
            ->first();

        return sha1(json_encode([
            'total' => (int)($snapshot?->total_settings ?? 0),
            'updated_at' => $snapshot?->latest_updated_at,
            'app_version' => Forkiva::$version,
            'locale' => locale(),
        ]));
    }

    /** @inheritDoc */
    public function getSettings(SettingSection $section): array
    {
        $settings = setting()->all();


        return match ($section) {
            SettingSection::General => [
                "supported_countries" => $settings['supported_countries'],
                "default_country" => $settings['default_country'],
                "supported_locales" => $settings['supported_locales'],
                "default_locale" => $settings['default_locale'],
                "default_timezone" => $settings['default_timezone'],
                "default_date_format" => $settings['default_date_format'],
                "default_time_format" => $settings['default_time_format'],
                "start_of_week" => $settings['start_of_week'],
                "end_of_week" => $settings['end_of_week'],
            ],
            SettingSection::Application => [
                "app_name" => unserialize(Setting::where('key', 'app_name')->first()->getRawOriginal('payload'))
            ],
            SettingSection::Currency => [
                "supported_currencies" => $settings['supported_currencies'],
                "default_currency" => $settings['default_currency'],
                "currency_rate_exchange_service" => $settings['currency_rate_exchange_service'] ?? null,
                "forge_api_key" => $settings['forge_api_key'] ?? null,
                "fixer_access_key" => $settings['fixer_access_key'] ?? null,
                "currency_data_feed_api_key" => $settings['currency_data_feed_api_key'] ?? null,
                "auto_refresh_currency_rates" => $settings['auto_refresh_currency_rates'] ?? false,
                "auto_refresh_currency_rate_frequency" => $settings['auto_refresh_currency_rate_frequency'] ?? null,
            ],
            SettingSection::Mail => [
                "mail_mailer" => $settings['mail_mailer'] ?? Mailer::Smtp->value,
                "mail_from_address" => $settings['mail_from_address'] ?? config('mail.from.address'),
                "mail_from_name" => $settings['mail_from_name'] ?? $settings['app_name'],
                "mail_host" => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
                "mail_port" => $settings['mail_port'] ?? config('mail.mailers.smtp.port'),
                "mail_username" => $settings['mail_username'] ?? config('mail.mailers.smtp.username'),
                "mail_password" => $settings['mail_password'] ?? config('mail.mailers.smtp.password'),
                "mail_encryption" => $settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'),
            ],
            SettingSection::Filesystem => [
                "default_filesystem_disk" => $settings['default_filesystem_disk'] ?? config('filesystems.default'),
                "default_private_filesystem_disk" => $settings['default_private_filesystem_disk'] ?? config('filesystems.default_private'),
                "filesystem_s3_public_use_path_style_endpoint" => $settings['filesystem_s3_public_use_path_style_endpoint'] ?? $settings['filesystem_s3_use_path_style_endpoint'] ?? null,
                "filesystem_s3_public_url" => $settings['filesystem_s3_public_url'] ?? $settings['filesystem_s3_url'] ?? null,
                "filesystem_s3_public_endpoint" => $settings['filesystem_s3_public_endpoint'] ?? $settings['filesystem_s3_endpoint'] ?? null,
                "filesystem_s3_public_region" => $settings['filesystem_s3_public_region'] ?? $settings['filesystem_s3_region'] ?? null,
                "filesystem_s3_public_bucket" => $settings['filesystem_s3_public_bucket'] ?? $settings['filesystem_s3_bucket'] ?? null,
                "filesystem_s3_private_use_path_style_endpoint" => $settings['filesystem_s3_private_use_path_style_endpoint'] ?? $settings['filesystem_s3_use_path_style_endpoint'] ?? null,
                "filesystem_s3_private_url" => $settings['filesystem_s3_private_url'] ?? $settings['filesystem_s3_url'] ?? null,
                "filesystem_s3_private_endpoint" => $settings['filesystem_s3_private_endpoint'] ?? $settings['filesystem_s3_endpoint'] ?? null,
                "filesystem_s3_private_region" => $settings['filesystem_s3_private_region'] ?? $settings['filesystem_s3_region'] ?? null,
                "filesystem_s3_private_bucket" => $settings['filesystem_s3_private_bucket'] ?? $settings['filesystem_s3_bucket'] ?? null,
                "encryptable" => [
                    "filesystem_s3_public_key" => $settings['filesystem_s3_public_key'] ?? $settings['filesystem_s3_key'] ?? null,
                    "filesystem_s3_public_secret" => $settings['filesystem_s3_public_secret'] ?? $settings['filesystem_s3_secret'] ?? null,
                    "filesystem_s3_private_key" => $settings['filesystem_s3_private_key'] ?? $settings['filesystem_s3_key'] ?? null,
                    "filesystem_s3_private_secret" => $settings['filesystem_s3_private_secret'] ?? $settings['filesystem_s3_secret'] ?? null,
                ]
            ],
            SettingSection::Logo => [
                "logo" => Media::getCacheMedia($settings['logo'] ?? null, true),
                "favicon" => Media::getCacheMedia($settings['favicon'] ?? null, true),
            ],
            SettingSection::Appearance => [
                "default_theme_mode" => $settings['default_theme_mode'] ?? 'light',
                "theme_primary_color" => $settings['theme_primary_color'] ?? '#F57C00',
                "theme_secondary_color" => $settings['theme_secondary_color'] ?? '#043A63',
                "theme_success_color" => $settings['theme_success_color'] ?? '#2ecc71',
                "theme_info_color" => $settings['theme_info_color'] ?? '#03C3EC',
                "theme_warning_color" => $settings['theme_warning_color'] ?? '#f1c40f',
                "theme_error_color" => $settings['theme_error_color'] ?? '#e74c3c',
            ],
            SettingSection::Kitchen => [
                "auto_refresh_enabled" => $settings['auto_refresh_enabled'] ?? false,
                "auto_refresh_mode" => $settings['auto_refresh_mode'] ?? null,
                "auto_refresh_interval" => $settings['auto_refresh_interval'] ?? null,
                "auto_refresh_pause_on_idle" => $settings['auto_refresh_pause_on_idle'] ?? false,
                "auto_refresh_idle_timeout" => $settings['auto_refresh_idle_timeout'] ?? null,
            ],
            SettingSection::Pwa => [
                "pwa_enabled" => (bool)($settings['pwa_enabled'] ?? false),
                "pwa_name" => $settings['pwa_name'] ?? 'Comanda',
                "pwa_short_name" => $settings['pwa_short_name'] ?? 'Comanda',
                "pwa_icon" => Media::getCacheMedia($settings['pwa_icon'] ?? null, true),
                "pwa_background_color" => $settings['pwa_background_color'] ?? '#FAF8F3',
                "pwa_theme_color" => $settings['pwa_theme_color'] ?? '#FAF8F3',
                "pwa_description" => $settings['pwa_description'] ?? null,
            ]
        };
    }

    /** @inheritDoc */
    public function getMeta(SettingSection $section): array
    {
        return match ($section) {
            SettingSection::General => [
                "countries" => Country::toList(),
                "locales" => Locale::toList(),
                "timezones" => TimeZone::toList(),
                "date_formats" => DateFormats::toList(),
                "time_formats" => TimeFormats::toList(),
                "days" => Day::toArrayTrans()
            ],
            SettingSection::Currency => [
                "currencies" => Currency::toList(),
                "frequencies" => Frequency::toArrayTrans(),
                "exchange_services" => ExchangeService::toArrayTrans()
            ],
            SettingSection::Mail => [
                "mailers" => Mailer::toArrayTrans(),
                "encryption_protocols" => MailEncryptionProtocol::toArrayTrans(),
            ],
            SettingSection::Filesystem => [
                "disks" => FilesystemDisk::toArrayTrans(),
                "private_disks" => [
                    ['id' => 'local', 'name' => __('support::enums.private_filesystem_disks.local')],
                    ['id' => 'public', 'name' => __('support::enums.private_filesystem_disks.public')],
                    ['id' => 's3', 'name' => __('support::enums.private_filesystem_disks.s3')],
                ],
            ],
            SettingSection::Appearance => [
                "theme_modes" => [
                    ['id' => 'light', 'name' => __('admin::navbar.theme_modes.light')],
                    ['id' => 'dark', 'name' => __('admin::navbar.theme_modes.dark')],
                ],
            ],
            SettingSection::Kitchen => [
                "modes" => AutoRefreshMode::toArrayTrans()
            ],
            default => []
        };
    }

    /** @inheritDoc */
    public function update(SettingSection $section, array $data): void
    {
        if ($section == SettingSection::Logo) {
            $oldLogo = setting('logo');
            if ($oldLogo != $data['logo']) {
                $base64 = null;
                $logo = Media::getCacheMedia($data['logo']);
                if ($logo) {
                    $base64 = base64_encode($logo->getContent());
                }
                Storage::disk(config('filesystems.default_private'))->put('logo.base64', $base64);
                $data['logo_mime_type'] = $logo?->mime_type;
            }
        }
        setting($data);
    }
}
