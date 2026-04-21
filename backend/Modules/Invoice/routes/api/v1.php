<?php


use Modules\Invoice\Http\Controllers\Api\V1\InvoiceController;

Route::controller(InvoiceController::class)
    ->prefix('invoices')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.invoices.index');
        Route::get('/{reference}/show', 'show')->middleware('can:admin.invoices.show');
    });
