<?php

use Modules\Printer\Http\Controllers\Api\V1\PrintAgentController;
use Modules\Printer\Http\Controllers\Api\V1\PrinterController;

Route::controller(PrinterController::class)
    ->prefix('printers')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.printers.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.printers.show|admin.printers.edit');
        Route::post('/', 'store')->middleware('can:admin.printers.create');
        Route::put('/{id}', 'update')->middleware('can:admin.printers.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.printers.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.printers.edit|admin.printers.create');
    });

Route::controller(PrintAgentController::class)
    ->prefix('print-agents')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.print_agents.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.print_agents.show|admin.print_agents.edit');
        Route::post('/', 'store')->middleware('can:admin.print_agents.create');
        Route::put('/{id}', 'update')->middleware('can:admin.print_agents.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.print_agents.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.print_agents.edit|admin.print_agents.create');
    });
