<?php

namespace Modules\Core\Providers;

use App\Forkiva;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Console\UpgradeCommand;
use Modules\Installer\Http\Middleware\CheckInstalled;
use Modules\Setting\Models\Setting;
use Modules\Setting\Repositories\SettingRepository;
use Spatie\Permission\Middleware\{PermissionMiddleware, RoleMiddleware, RoleOrPermissionMiddleware};

class CoreServiceProvider extends ServiceProvider
{

    /**
     * Core module specific middlewares.
     *
     * @var array
     */
    protected array $middlewares = [
        'role' => RoleMiddleware::class,
        'permission' => PermissionMiddleware::class,
        'role_or_permission' => RoleOrPermissionMiddleware::class,
        "checkInstalled" => CheckInstalled::class
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->setupAppCacheDriver();
        $this->registerSetting();
        $this->setupAppName();
        $this->setupMailConfig();
        $this->setupFilesystem();
        $this->setupAppLocale();
        $this->setupAppTimezone();
        $this->activityLog();
        $this->registerMiddleware();
        $this->registerCommands();
    }

    /**
     * Setup application cache driver.
     *
     * @return void
     */
    private function setupAppCacheDriver(): void
    {
        $this->app['config']->set('cache.default', Forkiva::cacheEnabled() ? config('cache.default') : 'array');
    }

    /**
     * Register setting binding.
     *
     * @return void
     */
    private function registerSetting(): void
    {
        $this->app->singleton('setting', fn() => new SettingRepository(Setting::allCached()));
    }

    /**
     * Setup application name.
     *
     * @return void
     */
    private function setupAppName(): void
    {
        $this->app['config']->set('app.name', setting('app_name', config('app.name')));
    }

    /**
     * Setup application mail config.
     *
     * @return void
     */
    private function setupMailConfig(): void
    {
        $this->app['config']->set('mail.default', setting('mail_mailer', 'smtp'));
        $this->app['config']->set('mail.from.address', setting('mail_from_address', config('mail.from.address')));
        $this->app['config']->set('mail.from.name', setting('mail_from_name', setting('app_name')));

        if ($this->app['config']->get('mail.default') === 'smtp') {
            $this->app['config']->set(
                'mail.mailers.smtp.host',
                setting('mail_host', config('mail.mailers.smtp.host'))
            );
            $this->app['config']->set(
                'mail.mailers.smtp.port',
                setting('mail_port', config('mail.mailers.smtp.port'))
            );
            $this->app['config']->set(
                'mail.mailers.smtp.username',
                setting('mail_username', config('mail.mailers.smtp.username'))
            );
            $this->app['config']->set(
                'mail.mailers.smtp.password',
                setting('mail_password', config('mail.mailers.smtp.password'))
            );
            $this->app['config']->set(
                'mail.mailers.smtp.encryption',
                setting('mail_encryption', config('mail.mailers.smtp.encryption'))
            );
        }
    }

    /**
     * Setup application filesystem config.
     *
     * @return void
     */
    private function setupFilesystem(): void
    {
        $disk = setting('default_filesystem_disk', config("filesystems.default"));
        $privateDisk = setting('default_private_filesystem_disk', config('filesystems.default_private'));

        $this->app['config']->set('filesystems.default', $disk === 's3' ? 's3' : $disk);
        $this->app['config']->set('filesystems.default_private', $privateDisk === 's3' ? 's3_private' : $privateDisk);
        $this->app['config']->set('media-library.disk_name', $disk);

        if ($disk === 's3') {
            $this->app['config']->set('filesystems.disks.s3.key', setting('filesystem_s3_public_key', setting('filesystem_s3_key')));
            $this->app['config']->set('filesystems.disks.s3.secret', setting('filesystem_s3_public_secret', setting('filesystem_s3_secret')));
            $this->app['config']->set('filesystems.disks.s3.region', setting('filesystem_s3_public_region', setting('filesystem_s3_region')));
            $this->app['config']->set('filesystems.disks.s3.bucket', setting('filesystem_s3_public_bucket', setting('filesystem_s3_bucket')));
            $this->app['config']->set('filesystems.disks.s3.url', setting('filesystem_s3_public_url', setting('filesystem_s3_url')));
            $this->app['config']->set('filesystems.disks.s3.endpoint', setting('filesystem_s3_public_endpoint', setting('filesystem_s3_endpoint')));
            $this->app['config']->set('filesystems.disks.s3.use_path_style_endpoint', (bool)setting('filesystem_s3_public_use_path_style_endpoint', setting('filesystem_s3_use_path_style_endpoint', false)));
        }

        if ($privateDisk === 's3') {
            $this->app['config']->set('filesystems.disks.s3_private.key', setting('filesystem_s3_private_key', setting('filesystem_s3_key')));
            $this->app['config']->set('filesystems.disks.s3_private.secret', setting('filesystem_s3_private_secret', setting('filesystem_s3_secret')));
            $this->app['config']->set('filesystems.disks.s3_private.region', setting('filesystem_s3_private_region', setting('filesystem_s3_region')));
            $this->app['config']->set('filesystems.disks.s3_private.bucket', setting('filesystem_s3_private_bucket', setting('filesystem_s3_bucket')));
            $this->app['config']->set('filesystems.disks.s3_private.url', setting('filesystem_s3_private_url', setting('filesystem_s3_url')));
            $this->app['config']->set('filesystems.disks.s3_private.endpoint', setting('filesystem_s3_private_endpoint', setting('filesystem_s3_endpoint')));
            $this->app['config']->set('filesystems.disks.s3_private.use_path_style_endpoint', (bool)setting('filesystem_s3_private_use_path_style_endpoint', setting('filesystem_s3_use_path_style_endpoint', false)));
        }
    }

    /**
     * Setup application locale.
     *
     * @return void
     */
    private function setupAppLocale(): void
    {
        $this->app['config']->set('app.locale', $defaultLocale = setting('default_locale', $this->app['config']->get('app.locale')));
        $this->app['config']->set('app.fallback_locale', $defaultLocale);
        $this->app->setLocale($defaultLocale);
    }

    /**
     * Setup application timezone.
     *
     * @return void
     */
    private function setupAppTimezone(): void
    {
        $timezone = setting('default_timezone') ?: config('app.timezone');

        date_default_timezone_set($timezone);

        $this->app['config']->set('app.timezone', $timezone);
    }

    /**
     * Enabled activity log.
     *
     * @return void
     */
    private function activityLog(): void
    {
        $this->app['config']->set(
            'activitylog.enabled',
            $this->app->runningInConsole()
                ? false
                : setting(
                'activity_log.enabled',
                $this->app['config']->get('activity_log.enabled')
            )
        );

        $this->app['config']->set(
            'activitylog.delete_records_older_than_days',
            setting(
                'activity_log.delete_records_older_than_days',
                $this->app['config']->get('activity_log.delete_records_older_than_days')
            )
        );
    }

    /**
     * Register the filters.
     *
     * @return void
     */
    private function registerMiddleware(): void
    {
        foreach ($this->middlewares as $name => $middleware) {
            $this->app['router']->aliasMiddleware($name, $middleware);
        }
    }

    /**
     * Register command
     *
     * @return void
     */
    private function registerCommands(): void
    {
        $this->commands([UpgradeCommand::class]);
    }
}
