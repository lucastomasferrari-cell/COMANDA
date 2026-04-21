<?php

namespace Modules\Setting\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Setting\Services\Setting\SettingService;
use Modules\Setting\Services\Setting\SettingServiceInterface;

class DeferredSettingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Boot the application events.
     */
    public function register(): void
    {
        $this->app->singleton(
            abstract: SettingServiceInterface::class,
            concrete: fn($app) => $app->make(SettingService::class)
        );
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            SettingServiceInterface::class,
        ];
    }
}
