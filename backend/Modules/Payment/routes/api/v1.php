<?php


use Modules\Payment\Http\Controllers\Api\V1\PaymentController;
use Modules\Payment\Http\Controllers\Api\V1\PaymentMethodItemController;

Route::controller(PaymentController::class)
    ->prefix('payments')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.payments.index');
        Route::get('/{id}', 'show')->middleware('can:admin.payments.show');
    });

// Payment methods configurables (admin > cobros > formas de pago).
// Report endpoint lo consume el dashboard (card de ventas por método)
// y PASE vía sync futura — gated por admin.payment_methods.index igual
// que los demás endpoints de lectura.
Route::controller(PaymentMethodItemController::class)
    ->prefix('payment-methods')
    ->group(function () {
        Route::get('/form-meta', 'formMeta')->middleware('can:admin.payment_methods.index');
        Route::get('/report', 'report')->middleware('can:admin.payment_methods.index');
        Route::get('/', 'index')->middleware('can:admin.payment_methods.index');
        Route::post('/', 'store')->middleware('can:admin.payment_methods.create');
        Route::get('/{id}', 'show')->middleware('can:admin.payment_methods.index');
        Route::put('/{id}', 'update')->middleware('can:admin.payment_methods.edit');
        Route::delete('/{id}', 'destroy')->middleware('can:admin.payment_methods.destroy');
    });
