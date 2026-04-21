<?php

use Modules\Menu\Http\Controllers\Api\V1\MenuController;
use Modules\Menu\Http\Controllers\Api\V1\OnlineMenuController;

Route::controller(MenuController::class)
    ->prefix('menus')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.menus.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.menus.show|admin.menus.edit');
        Route::post('/', 'store')->middleware('can:admin.menus.create');
        Route::put('/{id}', 'update')->middleware('can:admin.menus.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.menus.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.menus.edit|admin.menus.create');
    });

Route::controller(OnlineMenuController::class)
    ->prefix('online-menus')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.online_menus.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.online_menus.show|admin.online_menus.edit');
        Route::post('/', 'store')->middleware('can:admin.online_menus.create');
        Route::put('/{id}', 'update')->middleware('can:admin.online_menus.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.online_menus.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.online_menus.edit|admin.online_menus.create');
        Route::get('/{slug}/menu', 'getMenu')->withoutMiddleware('auth');
    });
