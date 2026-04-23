<?php

namespace Modules\AuditLog\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AuditLog\Console\Commands\CleanupAuditLogs;
use Modules\AuditLog\Console\Commands\DailyAntifraudReport;
use Modules\AuditLog\Console\Commands\ExpirePendingApprovals;

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
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'auditlog');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupAuditLogs::class,
                ExpirePendingApprovals::class,
                DailyAntifraudReport::class,
            ]);
        }
    }
}
