<?php

namespace Modules\Inventory\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Services\Ingredient\IngredientService;
use Modules\Inventory\Services\Ingredient\IngredientServiceInterface;
use Modules\Inventory\Services\InventoryAnalytics\InventoryAnalyticsService;
use Modules\Inventory\Services\InventoryAnalytics\InventoryAnalyticsServiceInterface;
use Modules\Inventory\Services\Purchase\PurchaseService;
use Modules\Inventory\Services\Purchase\PurchaseServiceInterface;
use Modules\Inventory\Services\StockMovement\StockMovementService;
use Modules\Inventory\Services\StockMovement\StockMovementServiceInterface;
use Modules\Inventory\Services\StockSync\StockSyncService;
use Modules\Inventory\Services\StockSync\StockSyncServiceInterface;
use Modules\Inventory\Services\Supplier\SupplierService;
use Modules\Inventory\Services\Supplier\SupplierServiceInterface;
use Modules\Inventory\Services\Unit\UnitService;
use Modules\Inventory\Services\Unit\UnitServiceInterface;

class DeferredInventoryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Boot the application events.
     */
    public function register(): void
    {
        $this->app->singleton(
            abstract: IngredientServiceInterface::class,
            concrete: fn($app) => $app->make(IngredientService::class)
        );

        $this->app->singleton(
            abstract: SupplierServiceInterface::class,
            concrete: fn($app) => $app->make(SupplierService::class)
        );

        $this->app->singleton(
            abstract: UnitServiceInterface::class,
            concrete: fn($app) => $app->make(UnitService::class)
        );

        $this->app->singleton(
            abstract: StockMovementServiceInterface::class,
            concrete: fn($app) => $app->make(StockMovementService::class)
        );

        $this->app->singleton(
            abstract: PurchaseServiceInterface::class,
            concrete: fn($app) => $app->make(PurchaseService::class)
        );

        $this->app->singleton(
            abstract: InventoryAnalyticsServiceInterface::class,
            concrete: fn($app) => $app->make(InventoryAnalyticsService::class)
        );

        $this->app->singleton(
            abstract: StockSyncServiceInterface::class,
            concrete: fn($app) => $app->make(StockSyncService::class)
        );

    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            SupplierServiceInterface::class,
            IngredientServiceInterface::class,
            UnitServiceInterface::class,
            StockMovementServiceInterface::class,
            PurchaseServiceInterface::class,
            InventoryAnalyticsServiceInterface::class,
            StockSyncServiceInterface::class
        ];
    }
}
