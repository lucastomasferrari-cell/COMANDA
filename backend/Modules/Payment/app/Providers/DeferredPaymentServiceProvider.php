<?php

namespace Modules\Payment\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Payment\Services\Payment\PaymentService;
use Modules\Payment\Services\Payment\PaymentServiceInterface;

class DeferredPaymentServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Boot the application events.
     */
    public function register(): void
    {
        $this->app->singleton(
            abstract: PaymentServiceInterface::class,
            concrete: fn($app) => $app->make(PaymentService::class)
        );
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            PaymentServiceInterface::class,
        ];
    }
}
