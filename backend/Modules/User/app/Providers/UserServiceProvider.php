<?php

namespace Modules\User\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\User\Console\Permission\{SyncDefaultRoles, SyncPermissions};

class UserServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
    }

    /**
     * Register command
     *
     * @return void
     */
    private function registerCommands(): void
    {
        $this->commands([
            SyncPermissions::class,
            SyncDefaultRoles::class
        ]);
    }
}
