<?php

use Modules\Media\Http\Controllers\Api\V1\MediaController;

Route::controller(MediaController::class)
    ->prefix('media')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.media.index');
        Route::get('/{id}/download', 'download')
            ->withoutMiddleware(['auth'])
            ->name('admin.media.download');
        Route::post('/', 'store')->middleware('can:admin.media.create');
        Route::post('/folder/store', 'storeFolder')->middleware('can:admin.media.create');
        Route::put('/{id}', 'update')->middleware('can:admin.media.edit');
        Route::delete('/{ids}', 'destroy')->middleware('can:admin.media.destroy');
    });
