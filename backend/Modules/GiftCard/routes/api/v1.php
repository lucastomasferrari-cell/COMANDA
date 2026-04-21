<?php

use Modules\GiftCard\Http\Controllers\Api\V1\GiftCardBatchController;
use Modules\GiftCard\Http\Controllers\Api\V1\GiftCardAnalyticsController;
use Modules\GiftCard\Http\Controllers\Api\V1\GiftCardController;
use Modules\GiftCard\Http\Controllers\Api\V1\GiftCardTransactionController;

Route::controller(GiftCardAnalyticsController::class)
    ->prefix('gift-cards-analytics')
    ->middleware('can:admin.gift_cards.analytics')
    ->group(function () {
        Route::get('/filters', 'filters');
        Route::get('/', 'index');
    });

Route::controller(GiftCardController::class)
    ->prefix('gift-cards')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.gift_cards.index');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.gift_cards.create|admin.gift_cards.edit');
        Route::get('/{id}', 'show')->middleware('permission:admin.gift_cards.show|admin.gift_cards.edit');
        Route::post('/', 'store')->middleware('can:admin.gift_cards.create');
        Route::put('/{id}', 'update')->middleware('can:admin.gift_cards.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.gift_cards.destroy');
        Route::get('/{id}/balance', 'balance')->middleware('permission:admin.gift_cards.show|admin.gift_cards.edit');
    });

Route::controller(GiftCardTransactionController::class)
    ->prefix('gift-card-transactions')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.gift_card_transactions.index');
        Route::get('/{id}', 'show')->middleware('can:admin.gift_card_transactions.show');
    });

Route::controller(GiftCardBatchController::class)
    ->prefix('gift-card-batches')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.gift_card_batches.index');
        Route::get('/{id}', 'show')->middleware('can:admin.gift_card_batches.show');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.gift_card_batches.index|admin.gift_card_batches.create');
        Route::post('/', 'store')->middleware('can:admin.gift_card_batches.create');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.gift_card_batches.destroy');
    });
