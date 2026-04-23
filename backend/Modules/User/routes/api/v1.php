<?php

use Modules\User\Http\Controllers\Api\V1\AccountController;
use Modules\User\Http\Controllers\Api\V1\AuthController;
use Modules\User\Http\Controllers\Api\V1\CustomerController;
use Modules\User\Http\Controllers\Api\V1\ManagerPinController;
use Modules\User\Http\Controllers\Api\V1\RoleController;
use Modules\User\Http\Controllers\Api\V1\UserController;

Route::controller(AuthController::class)
    ->prefix('auth')
    ->group(function () {

        Route::post('login', "login")
            ->withoutMiddleware(middleware: 'auth')
            ->middleware('throttle:login');

        Route::post('logout', "logout");
        Route::post('check', action: "check");
    });

Route::controller(ManagerPinController::class)
    ->prefix('auth/manager-pin')
    ->group(function () {
        Route::post('verify', 'verify')
            ->middleware('throttle:6,1'); // 6 intentos por minuto por IP — el lockout real vive en el service por user
        Route::post('self', 'setSelf'); // setear/actualizar mi propio PIN
    });

Route::controller(RoleController::class)
    ->prefix('roles')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.roles.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.roles.show|admin.roles.edit');
        Route::post('/', 'store')->middleware('can:admin.roles.create');
        Route::put('/{id}', 'update')->middleware('can:admin.roles.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.roles.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.roles.edit|admin.roles.create');
    });

Route::controller(UserController::class)
    ->prefix('users')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.users.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.users.show|admin.users.edit');
        Route::post('/', 'store')->middleware('can:admin.users.create');
        Route::put('/{id}', 'update')->middleware('can:admin.users.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.users.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.users.edit|admin.users.create');
    });

Route::controller(CustomerController::class)
    ->prefix('customers')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.customers.index');
        Route::get('/{id}', 'show')->middleware('permission:admin.customers.show|admin.customers.edit');
        Route::post('/', 'store')->middleware('can:admin.customers.create');
        Route::put('/{id}', 'update')->middleware('can:admin.customers.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.customers.destroy');
        Route::get('/form/meta', 'getFormMeta')->middleware('permission:admin.customers.edit|admin.customers.create');
    });

Route::controller(AccountController::class)
    ->prefix('accounts')
    ->group(function () {
        Route::get('me', 'me');
        Route::put('profile/update', 'updateProfile')->middleware('can:admin.profiles.edit');
        Route::put('password/update', 'updatePassword');
    });
