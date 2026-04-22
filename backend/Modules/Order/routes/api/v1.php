<?php

use Modules\Order\Http\Controllers\Api\V1\OrderController;
use Modules\Order\Http\Controllers\Api\V1\ReasonController;
use Modules\Printer\Enum\PrintContentType;

Route::controller(OrderController::class)
    ->prefix('orders')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.orders.index');
        Route::get('/{orderId}/show', 'show')->middleware('can:admin.orders.show');
        Route::prefix('/{orderId}/print')
            ->middleware('permission:admin.orders.print|admin.orders.create|admin.orders.receive_payment')
            ->group(function () {
                Route::get('/', 'printMeta');
                Route::get('{type}/preview', 'previewPrint')
                    ->whereIn('type', PrintContentType::values());
            });
        Route::get('/{cartId}/{orderId}/edit', 'edit')
            ->whereUuid('cartId')
            ->middleware('can:admin.orders.edit');
        Route::post('{cartId}', 'store')
            ->whereUuid('cartId')
            ->middleware('can:admin.orders.create');
        Route::put('/{cartId}/{orderId}/update', 'update')->middleware('can:admin.orders.edit');
        Route::post('/{orderId}/cancel', 'cancel')->middleware('can:admin.orders.cancel');
        Route::post('/{orderId}/refund', 'refund')->middleware('can:admin.orders.refund');
        Route::get('/{orderId}/update-status/meta', 'getUpdateStatusMeta')
            ->middleware('permission:admin.orders.cancel|admin.orders.refund');
        Route::get('active', 'activeOrders')
            ->middleware('can:admin.orders.active');
        Route::get('upcoming', 'upcomingOrders')
            ->middleware('can:admin.orders.upcoming');

        Route::prefix("{orderId}/payments")
            ->middleware('can:admin.orders.receive_payment')
            ->group(function () {
                Route::post('', 'storePayment');
                Route::get('meta', 'getPaymentMeta');
            });

        Route::patch('/{orderId}/move-to-next-status', 'moveToNextStatus')->middleware('can:admin.orders.update_status');
        Route::post('/{orderId}/products/custom', 'storeCustomProduct')->middleware('can:admin.orders.edit');
    });

Route::controller(ReasonController::class)
    ->prefix('reasons')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.reasons.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.reasons.show|admin.reasons.edit');
        Route::post('/', 'store')->middleware('can:admin.reasons.create');
        Route::put('/{id}', 'update')->middleware('can:admin.reasons.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.reasons.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.reasons.edit|admin.reasons.create');
    });
