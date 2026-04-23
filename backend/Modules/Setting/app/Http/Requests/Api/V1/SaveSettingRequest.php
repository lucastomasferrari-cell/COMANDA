<?php

namespace Modules\Setting\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\Currency\Currency;
use Modules\Currency\Enums\ExchangeService;
use Modules\Setting\Enums\AutoRefreshMode;
use Modules\Setting\Enums\SettingSection;
use Modules\Support\Country;
use Modules\Support\DateFormats;
use Modules\Support\Enums\Day;
use Modules\Support\Enums\FilesystemDisk;
use Modules\Support\Enums\Frequency;
use Modules\Support\Enums\MailEncryptionProtocol;
use Modules\Support\Enums\Mailer;
use Modules\Support\Locale;
use Modules\Support\TimeFormats;
use Modules\Support\TimeZone;

/**
 * @property SettingSection $section
 */
class SaveSettingRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return match ($this->section) {
            SettingSection::General => [
                "supported_countries" => "required|array",
                "supported_countries.*" => ["required", Rule::in(Country::codes())],
                'default_country' => 'required|in_array:supported_countries.*',
                "supported_locales" => "required|array",
                'supported_locales.*' => ['required', Rule::in(Locale::codes())],
                'default_locale' => 'required|in_array:supported_locales.*',
                'default_timezone' => ['required', Rule::in(TimeZone::keys())],
                "default_date_format" => ['required', Rule::in(DateFormats::keys())],
                "default_time_format" => ['required', Rule::in(TimeFormats::keys())],
                "start_of_week" => ["required", Rule::enum(Day::class)],
                "end_of_week" => ["required", Rule::enum(Day::class)],
            ],
            SettingSection::Application => [
                ...$this->getTranslationRules(["app_name" => "required|min:1|max:200"])
            ],
            SettingSection::Currency => [
                "supported_currencies" => "required|array",
                'supported_currencies.*' => ['required', Rule::in(Currency::codes())],
                'default_currency' => 'required|in_array:supported_currencies.*',
                "currency_rate_exchange_service" => ["nullable", Rule::enum(ExchangeService::class)],
                'fixer_access_key' => 'required_if:currency_rate_exchange_service,fixer',
                'forge_api_key' => 'required_if:currency_rate_exchange_service,forge',
                'currency_data_feed_api_key' => 'required_if:currency_rate_exchange_service,currency_data_feed',
                'auto_refresh_currency_rates' => 'required|boolean',
                'auto_refresh_currency_rate_frequency' => ['required_if:auto_refresh_currency_rates,1', 'nullable', Rule::enum(Frequency::class)],
            ],
            SettingSection::Mail => [
                "mail_mailer" => ['required', Rule::enum(Mailer::class)],
                "mail_from_address" => "required|email",
                "mail_from_name" => "required|string|min:1|max:200",
                "mail_host" => "required_if:mail_mailer,smtp|string",
                'mail_port' => "required_if:mail_mailer,smtp|integer|between:1,65535",
                "mail_username" => "nullable|string",
                "mail_password" => "nullable|string",
                "mail_encryption" => ["required_if:mail_mailer,smtp", Rule::enum(MailEncryptionProtocol::class)]
            ],
            SettingSection::Filesystem => [
                "default_filesystem_disk" => ["required", Rule::enum(FilesystemDisk::class)],
                "default_private_filesystem_disk" => ["required", Rule::in(['local', 'public', 's3'])],
                "filesystem_s3_public_use_path_style_endpoint" => "required_if:default_filesystem_disk,s3|nullable|boolean",
                "filesystem_s3_public_url" => "required_if:default_filesystem_disk,s3|nullable|string|url",
                "filesystem_s3_public_endpoint" => "required_if:default_filesystem_disk,s3|nullable|string",
                "filesystem_s3_public_region" => "required_if:default_filesystem_disk,s3|nullable|string",
                "filesystem_s3_public_bucket" => "required_if:default_filesystem_disk,s3|nullable|string",
                "encryptable.filesystem_s3_public_key" => "required_if:default_filesystem_disk,s3|nullable|string",
                "encryptable.filesystem_s3_public_secret" => "required_if:default_filesystem_disk,s3|nullable|string",
                "filesystem_s3_private_use_path_style_endpoint" => "required_if:default_private_filesystem_disk,s3|nullable|boolean",
                "filesystem_s3_private_url" => "required_if:default_private_filesystem_disk,s3|nullable|string|url",
                "filesystem_s3_private_endpoint" => "required_if:default_private_filesystem_disk,s3|nullable|string",
                "filesystem_s3_private_region" => "required_if:default_private_filesystem_disk,s3|nullable|string",
                "filesystem_s3_private_bucket" => "required_if:default_private_filesystem_disk,s3|nullable|string",
                "encryptable.filesystem_s3_private_key" => "required_if:default_private_filesystem_disk,s3|nullable|string",
                "encryptable.filesystem_s3_private_secret" => "required_if:default_private_filesystem_disk,s3|nullable|string",
            ],
            SettingSection::Logo => [
                "logo" => "bail|required|integer|exists:media,id",
                "favicon" => "bail|required|integer|exists:media,id",
            ],
            SettingSection::Appearance => [
                'default_theme_mode' => ['required', Rule::in(['light', 'dark'])],
                'theme_primary_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
                'theme_secondary_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
                'theme_success_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
                'theme_info_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
                'theme_warning_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
                'theme_error_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            ],
            SettingSection::Kitchen => [
                "auto_refresh_enabled" => "required|boolean",
                "auto_refresh_mode" => ["required_if:auto_refresh_enabled,true", "nullable", Rule::enum(AutoRefreshMode::class)],
                "auto_refresh_interval" => "required_if:auto_refresh_mode,smart_polling|nullable|integer|min:1000|max:600000",
                "auto_refresh_pause_on_idle" => "required_if:auto_refresh_mode,smart_polling|boolean",
                "auto_refresh_idle_timeout" => "required_if:auto_refresh_pause_on_idle,true|nullable|integer|min:10|max:3600",
            ],
            SettingSection::Pwa => [
                'pwa_enabled' => 'required|boolean',
                'pwa_name' => 'required_if:pwa_enabled,1|nullable|string|min:1|max:100',
                'pwa_short_name' => 'required_if:pwa_enabled,1|nullable|string|min:1|max:30',
                'pwa_icon' => 'required_if:pwa_enabled,1|nullable|integer|exists:media,id',
                'pwa_background_color' => ['required_if:pwa_enabled,1', 'nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
                'pwa_theme_color' => ['required_if:pwa_enabled,1', 'nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
                'pwa_description' => 'nullable|string|max:500',
            ],
            SettingSection::Antifraud => [
                'antifraud.discount_cashier_max_percent' => 'required|numeric|min:0|max:100',
                'antifraud.discount_manager_max_percent' => 'required|numeric|min:0|max:100',
                'antifraud.open_item_max_per_shift' => 'required|integer|min:0|max:999',
                'antifraud.open_item_max_amount_each' => 'required|numeric|min:0|max:9999999',
                'antifraud.open_item_max_total_per_shift' => 'required|numeric|min:0|max:9999999',
                'antifraud.session_close_justification_threshold' => 'required|numeric|min:0|max:9999999',
                'antifraud.session_close_manager_required_percent' => 'required|numeric|min:0|max:100',
                'antifraud.daily_report_enabled' => 'required|boolean',
                'antifraud.owner_alert_email' => 'nullable|email',
                'antifraud.daily_report_hour' => 'required|integer|min:0|max:23',
                'antifraud.allow_pending_without_manager' => 'required|boolean',
            ]
        };
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "setting::attributes.settings";
    }
}
