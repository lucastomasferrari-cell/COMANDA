<?php


use Modules\Payment\Http\Controllers\Api\V1\PaymentController;

Route::controller(PaymentController::class)
    ->prefix('payments')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.payments.index');
        Route::get('/{id}', 'show')->middleware('can:admin.payments.show');
    });
