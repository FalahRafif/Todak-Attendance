<?php

use App\Http\Controllers\Web\Hrd\AttendanceCorrectionController;
use App\Http\Controllers\Web\Hrd\Attendances\AttendanceApprovalController;
use App\Http\Controllers\Web\Hrd\Attendances\AttendanceIndexController;
use App\Http\Controllers\Web\Hrd\Attendances\AttendanceShowController;
use App\Http\Controllers\Web\Hrd\Attendances\IncompleteAttendanceController;
use App\Http\Controllers\Web\Hrd\Attendances\LateAttendanceController;
use App\Http\Controllers\Web\Hrd\Attendances\NotCheckedInController;
use App\Http\Controllers\Web\Hrd\Attendances\OutsideRadiusAttendanceController;
use App\Http\Controllers\Web\Hrd\DashboardController;
use App\Http\Controllers\Web\Hrd\EmployeeScheduleController;
use App\Http\Controllers\Web\Hrd\LeaveBalanceController;
use App\Http\Controllers\Web\Hrd\LeaveRequestController;
use App\Http\Controllers\Web\Hrd\Reports\DailyAttendanceReportController;
use App\Http\Controllers\Web\Hrd\Reports\MonthlyAttendanceReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('hrd')->name('hrd.')->middleware(['auth', 'role:HRD'])->group(function (): void {
    Route::redirect('/', '/hrd/dashboard')->name('home');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/attendances', AttendanceIndexController::class)->name('attendances');
    Route::get('/attendances/not-checked-in', NotCheckedInController::class)->name('attendances.not-checked-in');
    Route::get('/attendances/incomplete', IncompleteAttendanceController::class)->name('attendances.incomplete');
    Route::get('/attendances/late', LateAttendanceController::class)->name('attendances.late');
    Route::get('/attendances/outside-radius', OutsideRadiusAttendanceController::class)->name('attendances.outside-radius');
    Route::get('/attendances/{id}', AttendanceShowController::class)->name('attendances.show');
    Route::post('/attendances/{id}/approve', [AttendanceApprovalController::class, 'approve'])->name('attendances.approve');
    Route::post('/attendances/{id}/reject', [AttendanceApprovalController::class, 'reject'])->name('attendances.reject');
    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests');
    Route::get('/leave-requests/{id}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
    Route::post('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('/leave-requests/{id}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    Route::get('/attendance-corrections', [AttendanceCorrectionController::class, 'index'])->name('attendance-corrections');
    Route::get('/attendance-corrections/{id}', [AttendanceCorrectionController::class, 'show'])->name('attendance-corrections.show');
    Route::post('/attendance-corrections/{id}/approve', [AttendanceCorrectionController::class, 'approve'])->name('attendance-corrections.approve');
    Route::post('/attendance-corrections/{id}/reject', [AttendanceCorrectionController::class, 'reject'])->name('attendance-corrections.reject');
    Route::get('/employee-schedules', EmployeeScheduleController::class)->name('employee-schedules');
    Route::get('/leave-balances', LeaveBalanceController::class)->name('leave-balances');
    Route::get('/reports/daily-attendance', DailyAttendanceReportController::class)->name('reports.daily-attendance');
    Route::get('/reports/monthly-attendance', [MonthlyAttendanceReportController::class, 'index'])->name('reports.monthly-attendance');
    Route::post('/reports/monthly-attendance/generate', [MonthlyAttendanceReportController::class, 'generate'])->name('reports.monthly-attendance.generate');
});
