<?php

namespace Modules\Report\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Report\Services\Report\ReportService;
use Modules\Report\Services\Report\ReportServiceInterface;

class DeferredReportServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Boot the application events.
     */
    public function register(): void
    {
        $this->app->singleton(
            abstract: ReportServiceInterface::class,
            concrete: fn($app) => $app->make(ReportService::class)
        );
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            ReportServiceInterface::class,
        ];
    }
}
