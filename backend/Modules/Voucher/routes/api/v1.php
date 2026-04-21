<?php

use Modules\Voucher\Http\Controllers\Api\V1\VoucherController;

Route::controller(VoucherController::class)
    ->prefix('vouchers')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.vouchers.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.vouchers.show|admin.vouchers.edit');
        Route::post('/', 'store')->middleware('can:admin.vouchers.create');
        Route::put('/{id}', 'update')->middleware('can:admin.vouchers.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.vouchers.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.vouchers.edit|admin.vouchers.create');
    });
