<?php

namespace Modules\AuditLog\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AuditLog\Console\Commands\CleanupAuditLogs;

class AuditLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/config.php',
            'auditlog'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupAuditLogs::class,
            ]);
        }
    }
}
