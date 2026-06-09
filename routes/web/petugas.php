<?php

use App\Http\Controllers\Web\Hrd\HrdModuleController;
use Illuminate\Support\Facades\Route;

Route::prefix('hrd')->name('hrd.')->middleware(['auth', 'role:HRD'])->group(function (): void {
    Route::redirect('/', '/hrd/dashboard')->name('home');
    Route::get('/dashboard', [HrdModuleController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendances', [HrdModuleController::class, 'attendances'])->name('attendances');
    Route::get('/attendances/not-checked-in', [HrdModuleController::class, 'notCheckedIn'])->name('attendances.not-checked-in');
    Route::get('/attendances/incomplete', [HrdModuleController::class, 'incomplete'])->name('attendances.incomplete');
    Route::get('/attendances/late', [HrdModuleController::class, 'late'])->name('attendances.late');
    Route::get('/attendances/outside-radius', [HrdModuleController::class, 'outsideRadius'])->name('attendances.outside-radius');
    Route::get('/attendances/{id}', [HrdModuleController::class, 'showAttendance'])->name('attendances.show');
    Route::post('/attendances/{id}/approve', [HrdModuleController::class, 'approveAttendance'])->name('attendances.approve');
    Route::post('/attendances/{id}/reject', [HrdModuleController::class, 'rejectAttendance'])->name('attendances.reject');
    Route::get('/leave-requests', [HrdModuleController::class, 'leaveRequests'])->name('leave-requests');
    Route::get('/leave-requests/{id}', [HrdModuleController::class, 'showLeaveRequest'])->name('leave-requests.show');
    Route::post('/leave-requests/{id}/approve', [HrdModuleController::class, 'approveLeaveRequest'])->name('leave-requests.approve');
    Route::post('/leave-requests/{id}/reject', [HrdModuleController::class, 'rejectLeaveRequest'])->name('leave-requests.reject');
    Route::get('/attendance-corrections', [HrdModuleController::class, 'attendanceCorrections'])->name('attendance-corrections');
    Route::get('/attendance-corrections/{id}', [HrdModuleController::class, 'showAttendanceCorrection'])->name('attendance-corrections.show');
    Route::post('/attendance-corrections/{id}/approve', [HrdModuleController::class, 'approveAttendanceCorrection'])->name('attendance-corrections.approve');
    Route::post('/attendance-corrections/{id}/reject', [HrdModuleController::class, 'rejectAttendanceCorrection'])->name('attendance-corrections.reject');
    Route::get('/employee-schedules', [HrdModuleController::class, 'employeeSchedules'])->name('employee-schedules');
    Route::get('/leave-balances', [HrdModuleController::class, 'leaveBalances'])->name('leave-balances');
    Route::get('/reports/daily-attendance', [HrdModuleController::class, 'dailyReport'])->name('reports.daily-attendance');
    Route::get('/reports/daily-attendance/export', [HrdModuleController::class, 'exportDailyReport'])->name('reports.daily-attendance.export');
    Route::get('/reports/monthly-attendance', [HrdModuleController::class, 'monthlyReport'])->name('reports.monthly-attendance');
    Route::post('/reports/monthly-attendance/generate', [HrdModuleController::class, 'generateMonthlyReport'])->name('reports.monthly-attendance.generate');
    Route::get('/reports/monthly-attendance/export', [HrdModuleController::class, 'exportMonthlyReport'])->name('reports.monthly-attendance.export');
});
