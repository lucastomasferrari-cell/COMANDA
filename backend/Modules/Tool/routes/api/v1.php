<?php

use Modules\Tool\Http\Controllers\Api\V1\DatabaseController;

Route::controller(DatabaseController::class)
    ->prefix('tools/database')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.database_tools.index');
        Route::get('/download/{fileName}', 'download')->middleware('can:admin.database_tools.download');
        Route::post('/backup', 'backup')->middleware('can:admin.database_tools.backup');
        Route::post('/restore', 'restore')->middleware('can:admin.database_tools.restore');
        Route::post('/restore/{fileName}', 'restoreFromBackup')->middleware('can:admin.database_tools.restore');
    });
