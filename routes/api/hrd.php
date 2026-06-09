<?php

use App\Http\Controllers\Api\Hrd\Reports\AttendanceReportExportController;
use Illuminate\Support\Facades\Route;

Route::prefix('hrd')
    ->middleware(['web', 'auth', 'role:HRD'])
    ->name('api.hrd.')
    ->group(function (): void {
        Route::get('/reports/daily-attendance/export', [AttendanceReportExportController::class, 'daily'])->name('reports.daily-attendance.export');
        Route::get('/reports/monthly-attendance/export', [AttendanceReportExportController::class, 'monthly'])->name('reports.monthly-attendance.export');
    });
