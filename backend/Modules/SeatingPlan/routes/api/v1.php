<?php

use Modules\SeatingPlan\Http\Controllers\Api\V1\FloorController;
use Modules\SeatingPlan\Http\Controllers\Api\V1\TableController;
use Modules\SeatingPlan\Http\Controllers\Api\V1\TableMergeController;
use Modules\SeatingPlan\Http\Controllers\Api\V1\TableViewerController;
use Modules\SeatingPlan\Http\Controllers\Api\V1\ZoneController;

Route::controller(FloorController::class)
    ->prefix('floors')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.floors.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.floors.show|admin.floors.edit');
        Route::post('/', 'store')->middleware('can:admin.floors.create');
        Route::put('/{id}', 'update')->middleware('can:admin.floors.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.floors.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.floors.edit|admin.floors.create');
    });

Route::controller(TableMergeController::class)
    ->prefix('table-merges')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.table_merges.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.table_merges.show');
    });

Route::controller(ZoneController::class)
    ->prefix('zones')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.zones.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.zones.show|admin.zones.edit');
        Route::post('/', 'store')->middleware('can:admin.zones.create');
        Route::put('/{id}', 'update')->middleware('can:admin.zones.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.zones.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.zones.edit|admin.zones.create');
    });

Route::controller(TableController::class)
    ->prefix('tables')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.tables.index');
        Route::patch('/positions', 'updatePositions')->middleware('can:admin.tables.edit');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.tables.edit|admin.tables.create');
        Route::get('/{id}', 'show')->middleware('permission:admin.tables.show|admin.tables.edit');
        Route::get('/{id}/status-logs', 'getStatusLogs')->middleware('can:admin.tables.show');
        Route::post('/', 'store')->middleware('can:admin.tables.create');
        Route::put('/{id}', 'update')->middleware('can:admin.tables.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.tables.destroy');
    });

Route::controller(TableViewerController::class)
    ->prefix('tables/viewer')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.tables.viewer');
        Route::get('/{id}', 'show')->middleware('can:admin.tables.show');
        Route::patch('/{id}/assign-waiter', 'assignWaiter')->middleware('can:admin.tables.assign_waiter');
        Route::post('/{id}/merge', 'merge')->middleware('can:admin.tables.merge');
        Route::get('/{id}/merge/meta', 'getMergeMeta')->middleware('can:admin.tables.merge');
        Route::patch('/{id}/make-available', 'makeAsAvailable')->middleware('can:admin.tables.update_status');
        Route::post('/{id}/split', 'splitTable')->middleware('can:admin.tables.split');
    });
