<?php

use Modules\Cart\Http\Controllers\Api\V1\CartController;
use Modules\Cart\Http\Controllers\Api\V1\CartCustomerController;
use Modules\Cart\Http\Controllers\Api\V1\CartDiscountController;
use Modules\Cart\Http\Controllers\Api\V1\CartGiftController;
use Modules\Cart\Http\Controllers\Api\V1\CartItemController;
use Modules\Cart\Http\Controllers\Api\V1\CartOrderTypeController;
use Modules\Cart\Http\Controllers\Api\V1\CartVoucherController;
use Modules\Order\Enums\OrderType;


Route::prefix('cart/{cartId}')
    ->whereUuid('cartId')
    ->group(function () {
        Route::controller(CartController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('meta', 'getMeta');
                Route::delete('clear', 'clear');
            });

        Route::controller(CartItemController::class)
            ->prefix('items')
            ->group(function () {
                Route::post('', 'store');
                Route::put('{itemId}', 'update');
                Route::delete('{itemId}', 'destroy');
                Route::post('{itemId}/action', 'storeAction');
                Route::delete('{itemId}/action', 'destroyAction');
            });

        Route::controller(CartOrderTypeController::class)
            ->prefix('order-types')
            ->group(function () {
                Route::post('{type}', 'store')
                    ->whereIn('type', OrderType::values());
                Route::delete('', 'destroy');
            });

        Route::controller(CartCustomerController::class)
            ->prefix('customers')
            ->group(function () {
                Route::post('{id}', 'store');
                Route::delete('', 'destroy');
            });

        Route::controller(CartDiscountController::class)
            ->prefix('discounts')
            ->group(function () {
                Route::post('{id}', 'store');
                Route::delete('', 'destroy');
            });

        Route::post('vouchers', [CartVoucherController::class, 'store']);
        Route::post('gifts/{id}', [CartGiftController::class, 'store']);
    });
