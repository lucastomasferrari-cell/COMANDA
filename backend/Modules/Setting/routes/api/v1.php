<?php

use Modules\Setting\Enums\SettingSection;
use Modules\Setting\Http\Controllers\Api\V1\SettingController;

Route::controller(SettingController::class)
    ->group(function () {
        Route::get('app/settings', "getAppSettings")->withoutMiddleware('auth');
        Route::get('app/boot-meta', "getAppBootMeta")->withoutMiddleware('auth');
        Route::prefix("settings/{section}")
            ->whereIn("section", SettingSection::values())
            ->middleware(['can:admin.settings.edit'])
            ->group(function () {
                Route::get('/', "index");
                Route::put('/update', "update");
            });

        Route::post('settings/antifraud/send-test-report', "sendAntifraudTestReport")
            ->middleware(['can:admin.settings.edit']);
    });
