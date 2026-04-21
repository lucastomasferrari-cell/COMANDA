<?php

use Modules\Product\Http\Controllers\Api\V1\ProductController;

Route::controller(ProductController::class)
    ->prefix('products')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.products.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.products.show|admin.products.edit');
        Route::post('/', 'store')->middleware('can:admin.products.create');
        Route::put('/{id}', 'update')->middleware('can:admin.products.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.products.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.products.edit|admin.products.create');
    });
