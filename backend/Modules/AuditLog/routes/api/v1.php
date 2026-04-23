<?php

use Modules\AuditLog\Http\Controllers\Api\V1\AntiFraudDashboardController;
use Modules\AuditLog\Http\Controllers\Api\V1\AuditLogController;
use Modules\AuditLog\Http\Controllers\Api\V1\PendingApprovalController;

Route::controller(AuditLogController::class)
    ->prefix('audit-logs')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.audit_logs.index');
        Route::get('/{id}', 'show')->middleware('can:admin.audit_logs.show');
    });

Route::controller(PendingApprovalController::class)
    ->prefix('pending-approvals')
    ->group(function () {
        Route::get('/', 'index')->middleware('can:admin.audit_logs.index');
        Route::patch('/{id}/approve', 'approve')->middleware('can:admin.audit_logs.index');
        Route::patch('/{id}/reject', 'reject')->middleware('can:admin.audit_logs.index');
    });

Route::controller(AntiFraudDashboardController::class)
    ->prefix('antifraud')
    ->group(function () {
        Route::get('/summary', 'summary')->middleware('can:admin.audit_logs.index');
    });
