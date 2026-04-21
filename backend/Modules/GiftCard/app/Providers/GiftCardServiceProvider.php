<?php

namespace Modules\GiftCard\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\GiftCard\Console\ExpireGiftCardsCommand;

class GiftCardServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerScheduler();
    }

    /**
     * Register command
     *
     * @return void
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExpireGiftCardsCommand::class
            ]);
        }
    }

    /**
     * Register scheduler
     *
     * @return void
     */
    private function registerScheduler(): void
    {
        $this->app->booted(function ($app) {
            $schedule = $app[Schedule::class];

            $schedule->command(ExpireGiftCardsCommand::class, ['--queue'])
                ->dailyAt('04:00')
                ->onOneServer()
                ->withoutOverlapping();
        });
    }
}
