<?php

use App\Http\Controllers\Web\Employee\Attendance\AttendanceCalendarController;
use App\Http\Controllers\Web\Employee\Attendance\AttendanceController;
use App\Http\Controllers\Web\Employee\Attendance\AttendanceHistoryController;
use App\Http\Controllers\Web\Employee\DashboardController;
use App\Http\Controllers\Web\Employee\ProfileController;
use App\Http\Controllers\Web\Employee\Requests\AttendanceCorrectionController;
use App\Http\Controllers\Web\Employee\Requests\LeaveRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('employee')->name('employee.')->middleware(['auth', 'role:Employee'])->group(function (): void {
    Route::redirect('/', '/employee/dashboard')->name('home');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/attendance', [AttendanceController::class, 'today'])->name('attendance');
    Route::get('/attendance/check-in', [AttendanceController::class, 'checkInForm'])->name('attendance.check-in');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in.store');
    Route::get('/attendance/check-out', [AttendanceController::class, 'checkOutForm'])->name('attendance.check-out');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out.store');
    Route::get('/attendance/calendar', AttendanceCalendarController::class)->name('attendance.calendar');
    Route::get('/attendance/history', [AttendanceHistoryController::class, 'index'])->name('attendance.history');
    Route::get('/attendance/history/{id}', [AttendanceHistoryController::class, 'show'])->name('attendance.history.show');
    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests');
    Route::get('/leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
    Route::get('/leave-requests/{id}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
    Route::post('/leave-requests/{id}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
    Route::get('/attendance-corrections', [AttendanceCorrectionController::class, 'index'])->name('attendance-corrections');
    Route::get('/attendance-corrections/create', [AttendanceCorrectionController::class, 'create'])->name('attendance-corrections.create');
    Route::post('/attendance-corrections', [AttendanceCorrectionController::class, 'store'])->name('attendance-corrections.store');
    Route::get('/attendance-corrections/{id}', [AttendanceCorrectionController::class, 'show'])->name('attendance-corrections.show');
    Route::post('/attendance-corrections/{id}/cancel', [AttendanceCorrectionController::class, 'cancel'])->name('attendance-corrections.cancel');
    Route::get('/profile', ProfileController::class)->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
