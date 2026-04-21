<?php

use Modules\Report\Http\Controllers\Api\V1\ReportController;

Route::controller(ReportController::class)
    ->prefix('reports/{report}')
    ->middleware('can:admin.reports.index')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/export/{method}', 'export');
    });
