<?php

namespace Modules\Tool\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Tool\Services\Database\DatabaseService;
use Modules\Tool\Services\Database\DatabaseServiceInterface;

class DeferredToolServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            abstract: DatabaseServiceInterface::class,
            concrete: fn($app) => $app->make(DatabaseService::class)
        );
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            DatabaseServiceInterface::class,
        ];
    }
}
