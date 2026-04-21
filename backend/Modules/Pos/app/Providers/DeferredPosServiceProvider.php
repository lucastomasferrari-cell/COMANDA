<?php

namespace Modules\Pos\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Pos\Services\KitchenViewer\KitchenViewerService;
use Modules\Pos\Services\KitchenViewer\KitchenViewerServiceInterface;
use Modules\Pos\Services\PosCashMovement\PosCashMovementService;
use Modules\Pos\Services\PosCashMovement\PosCashMovementServiceInterface;
use Modules\Pos\Services\PosCustomerViewer\PosCustomerViewerService;
use Modules\Pos\Services\PosCustomerViewer\PosCustomerViewerServiceInterface;
use Modules\Pos\Services\PosRegister\PosRegisterService;
use Modules\Pos\Services\PosRegister\PosRegisterServiceInterface;
use Modules\Pos\Services\PosSession\PosSessionService;
use Modules\Pos\Services\PosSession\PosSessionServiceInterface;
use Modules\Pos\Services\PosViewer\PosViewerService;
use Modules\Pos\Services\PosViewer\PosViewerServiceInterface;

class DeferredPosServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Boot the application events.
     */
    public function register(): void
    {
        $this->app->singleton(
            abstract: PosRegisterServiceInterface::class,
            concrete: fn($app) => $app->make(PosRegisterService::class)
        );

        $this->app->singleton(
            abstract: PosSessionServiceInterface::class,
            concrete: fn($app) => $app->make(PosSessionService::class)
        );

        $this->app->singleton(
            abstract: PosCashMovementServiceInterface::class,
            concrete: fn($app) => $app->make(PosCashMovementService::class)
        );

        $this->app->singleton(
            abstract: PosViewerServiceInterface::class,
            concrete: fn($app) => $app->make(PosViewerService::class)
        );

        $this->app->singleton(
            abstract: KitchenViewerServiceInterface::class,
            concrete: fn($app) => $app->make(KitchenViewerService::class)
        );

        $this->app->singleton(
            abstract: PosCustomerViewerServiceInterface::class,
            concrete: fn($app) => $app->make(PosCustomerViewerService::class)
        );

    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            PosRegisterServiceInterface::class,
            PosSessionServiceInterface::class,
            PosCashMovementServiceInterface::class,
            PosViewerServiceInterface::class,
            KitchenViewerServiceInterface::class,
            PosCustomerViewerServiceInterface::class
        ];
    }
}
