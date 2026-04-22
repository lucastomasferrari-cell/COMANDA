<?php

use Modules\AuditLog\Http\Controllers\Api\V1\AuditLogController;

Route::controller(AuditLogController::class)
    ->prefix('audit-logs')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.audit_logs.index');
        Route::get('/{id}', 'show')->middleware('can:admin.audit_logs.show');
    });
