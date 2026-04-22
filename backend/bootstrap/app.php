<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Support\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(["checkInstalled"]);
        $middleware->append(\App\Http\Middleware\ReadOnlyBranchMutations::class);
        $middleware->append(\App\Http\Middleware\ValidateSingleBranchInvariant::class);
        $middleware->append(\App\Http\Middleware\IdempotencyKey::class);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Cleanup diario de idempotency_keys vencidas. Evita que la tabla
        // crezca sin limite en instalaciones con trafico alto. Requiere
        // `php artisan schedule:work` o un cron llamando `schedule:run` c/
        // minuto para que se ejecute.
        $schedule->call(function () {
            DB::table('idempotency_keys')
                ->where('expires_at', '<', now())
                ->delete();
        })->dailyAt('03:00')->name('idempotency_keys.cleanup')->onOneServer();

        // Archiva + elimina audit logs > 1 año no fiscales. Los fiscales
        // (AFIP 10 años) NUNCA se tocan acá. El archive queda en
        // storage/app/audit-archive/YYYY-MM-DD.jsonl.gz.
        $schedule->command('audit:cleanup')
            ->dailyAt('03:15')
            ->name('audit_logs.cleanup')
            ->onOneServer();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (app()->isProduction() && $request->wantsJson() && $exception->getPrevious() instanceof ModelNotFoundException) {
                return ApiResponse::errors(
                    errors: null,
                    message: __('core::exceptions.model_not_found'),
                    code: Response::HTTP_NOT_FOUND
                );
            }
            return null;
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if ($request->wantsJson()) {
                return ApiResponse::errors(
                    errors: $exception->errors(),
                    message: __('core::exceptions.unprocessable'),
                    code: Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        });
    })
    ->create();
