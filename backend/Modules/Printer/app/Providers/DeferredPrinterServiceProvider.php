<?php

namespace Modules\Printer\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Printer\Services\Agent\PrintAgentService;
use Modules\Printer\Services\Agent\PrintAgentServiceInterface;
use Modules\Printer\Services\Printer\PrinterService;
use Modules\Printer\Services\Printer\PrinterServiceInterface;
use Modules\Printer\Services\Render\PrintRenderService;
use Modules\Printer\Services\Render\PrintRenderServiceInterface;

class DeferredPrinterServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Boot the application events.
     */
    public function register(): void
    {
        $this->app->singleton(
            abstract: PrinterServiceInterface::class,
            concrete: fn($app) => $app->make(PrinterService::class)
        );

        $this->app->singleton(
            abstract: PrintAgentServiceInterface::class,
            concrete: fn($app) => $app->make(PrintAgentService::class)
        );

        $this->app->singleton(
            abstract: PrintRenderServiceInterface::class,
            concrete: fn($app) => $app->make(PrintRenderService::class)
        );

    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            PrinterServiceInterface::class,
            PrintAgentServiceInterface::class,
            PrintRenderServiceInterface::class
        ];
    }
}
