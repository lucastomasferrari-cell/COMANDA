<?php

namespace Modules\GiftCard\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\GiftCard\Services\GiftCard\GiftCardService;
use Modules\GiftCard\Services\GiftCard\GiftCardServiceInterface;
use Modules\GiftCard\Services\GiftCardAnalytics\GiftCardAnalyticsService;
use Modules\GiftCard\Services\GiftCardAnalytics\GiftCardAnalyticsServiceInterface;
use Modules\GiftCard\Services\GiftCardBatch\GiftCardBatchService;
use Modules\GiftCard\Services\GiftCardBatch\GiftCardBatchServiceInterface;
use Modules\GiftCard\Services\GiftCardCode\GiftCardCodeService;
use Modules\GiftCard\Services\GiftCardCode\GiftCardCodeServiceInterface;
use Modules\GiftCard\Services\GiftCardTransaction\GiftCardTransactionService;
use Modules\GiftCard\Services\GiftCardTransaction\GiftCardTransactionServiceInterface;

class DeferredGiftCardServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(
            abstract: GiftCardTransactionServiceInterface::class,
            concrete: fn($app) => $app->make(GiftCardTransactionService::class)
        );

        $this->app->singleton(
            abstract: GiftCardServiceInterface::class,
            concrete: fn($app) => $app->make(GiftCardService::class)
        );

        $this->app->singleton(
            abstract: GiftCardCodeServiceInterface::class,
            concrete: fn($app) => $app->make(GiftCardCodeService::class)
        );

        $this->app->singleton(
            abstract: GiftCardBatchServiceInterface::class,
            concrete: fn($app) => $app->make(GiftCardBatchService::class)
        );

        $this->app->singleton(
            abstract: GiftCardAnalyticsServiceInterface::class,
            concrete: fn($app) => $app->make(GiftCardAnalyticsService::class)
        );
    }

    public function provides(): array
    {
        return [
            GiftCardServiceInterface::class,
            GiftCardCodeServiceInterface::class,
            GiftCardTransactionServiceInterface::class,
            GiftCardBatchServiceInterface::class,
            GiftCardAnalyticsServiceInterface::class,
        ];
    }
}
