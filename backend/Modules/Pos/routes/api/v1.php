<?php

use Modules\Pos\Http\Controllers\Api\V1\KitchenViewerController;
use Modules\Pos\Http\Controllers\Api\V1\PosCashMovementController;
use Modules\Pos\Http\Controllers\Api\V1\PosCustomerViewerController;
use Modules\Pos\Http\Controllers\Api\V1\PosRegisterController;
use Modules\Pos\Http\Controllers\Api\V1\PosSessionController;
use Modules\Pos\Http\Controllers\Api\V1\PosViewerController;

Route::controller(PosRegisterController::class)
    ->prefix('pos/registers')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.pos_registers.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.pos_registers.show|admin.pos_registers.edit');
        Route::post('/', 'store')->middleware('can:admin.pos_registers.create');
        Route::put('/{id}', 'update')->middleware('can:admin.pos_registers.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.pos_registers.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.pos_registers.edit|admin.pos_registers.create');
    });

Route::controller(PosSessionController::class)
    ->prefix('pos/sessions')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.pos_sessions.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.pos_sessions.show');
        Route::post('/open', 'open')->middleware('can:admin.pos_sessions.open');
        Route::put('/{id}/close', 'close')->middleware('can:admin.pos_sessions.close');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.pos_sessions.open');
    });

Route::controller(PosCashMovementController::class)
    ->prefix('pos/cash-movements')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.pos_cash_movements.index');
        Route::post('/', 'store')->middleware('can:admin.pos_cash_movements.create');
        Route::get('/{id}', 'show')->middleware('permission:admin.pos_cash_movements.show');
    });


Route::controller(KitchenViewerController::class)
    ->prefix('pos/kitchen-viewer')
    ->middleware('can:admin.pos.kitchen_viewer')
    ->group(function () {
        Route::get('configuration', 'configuration');
        Route::get('orders', 'orders');
        Route::patch('{orderId}/move-to-next-status', 'updateOrderProductStatus');
    });


Route::controller(PosViewerController::class)
    ->prefix('pos/viewer/{cartId}')
    ->whereUuid('cartId')
    ->middleware('can:admin.pos.index')
    ->group(function () {
        Route::get('configuration', 'configuration');
        Route::get('menu-items/{menuId}', 'menuItems')
            ->whereNumber('menuId');
    });

Route::controller(PosCustomerViewerController::class)
    ->prefix('pos/customer/viewer/{cartId}')
    ->whereUuid('cartId')
    ->withoutMiddleware('auth')
    ->group(function () {
        Route::get('/', 'stream');
    });
