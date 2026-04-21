<?php


use Modules\Inventory\Http\Controllers\Api\V1\IngredientController;
use Modules\Inventory\Http\Controllers\Api\V1\InventoryAnalyticsController;
use Modules\Inventory\Http\Controllers\Api\V1\PurchaseController;
use Modules\Inventory\Http\Controllers\Api\V1\StockMovementController;
use Modules\Inventory\Http\Controllers\Api\V1\SupplierController;
use Modules\Inventory\Http\Controllers\Api\V1\UnitController;

Route::controller(SupplierController::class)
    ->prefix('suppliers')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.suppliers.index');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.suppliers.edit|admin.suppliers.create');
        Route::get('/{id}', 'show')->middleware('permission:admin.suppliers.show|admin.suppliers.edit');
        Route::post('/', 'store')->middleware('can:admin.suppliers.create');
        Route::put('/{id}', 'update')->middleware('can:admin.suppliers.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.suppliers.destroy');
    });

Route::controller(UnitController::class)
    ->prefix('units')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.units.index');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.units.edit|admin.units.create');
        Route::get('/{id}', 'show')->middleware('permission:admin.units.show|admin.units.edit');
        Route::post('/', 'store')->middleware('can:admin.units.create');
        Route::put('/{id}', 'update')->middleware('can:admin.units.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.units.destroy');
    });

Route::controller(IngredientController::class)
    ->prefix('ingredients')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.ingredients.index');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.ingredients.edit|admin.ingredients.create');
        Route::get('/{id}', 'show')->middleware('permission:admin.ingredients.show|admin.ingredients.edit');
        Route::post('/', 'store')->middleware('can:admin.ingredients.create');
        Route::put('/{id}', 'update')->middleware('can:admin.ingredients.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.ingredients.destroy');
    });

Route::controller(StockMovementController::class)
    ->prefix('stock-movements')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.stock_movements.index');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.stock_movements.edit|admin.stock_movements.create');
        Route::get('/{id}', 'show')->middleware('permission:admin.stock_movements.show|admin.stock_movements.edit');
        Route::post('/', 'store')->middleware('can:admin.stock_movements.create');
        Route::put('/{id}', 'update')->middleware('can:admin.stock_movements.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.stock_movements.destroy');
    });

Route::controller(PurchaseController::class)
    ->prefix('purchases')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.purchases.index');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.purchases.edit|admin.purchases.create');
        Route::get('/{id}', 'show')->middleware('permission:admin.purchases.show|admin.purchases.edit')->name('admin.purchases.show');
        Route::post('/', 'store')->middleware('can:admin.purchases.create');
        Route::put('/{id}', 'update')->middleware('can:admin.purchases.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.purchases.destroy');
        Route::post('/{id}/mark-as-received', 'markAsReceived')->middleware('can:admin.purchases.mark_as_received');
    });

Route::controller(InventoryAnalyticsController::class)
    ->prefix('inventories/analytics')
    ->middleware('can:admin.inventories.analytics')
    ->group(function () {
        Route::get('meta/data', 'getMetaData');
        Route::get('top-suppliers', 'topSuppliers');
        Route::get('ingredient-purchases', 'ingredientPurchases');
        Route::get('stock-movement-summary', 'stockMovementSummary');
        Route::get('wastage-and-spoilage', 'wastageAndSpoilage');
        Route::get('purchase-status-summary', 'purchaseStatusSummary');
        Route::get('low-stock-ingredients', 'lowStockIngredients');
        Route::get('fast-moving-ingredients', 'fastMovingIngredients');
        Route::get('most-wasted-ingredients', 'mostWastedIngredients');

    });
