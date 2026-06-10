<?php

use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\Modules\ApplicationParameterController;
use App\Http\Controllers\Web\Admin\Modules\DepartmentController;
use App\Http\Controllers\Web\Admin\Modules\EmployeeController;
use App\Http\Controllers\Web\Admin\Modules\HolidayController;
use App\Http\Controllers\Web\Admin\Modules\PositionController;
use App\Http\Controllers\Web\Admin\Modules\ShiftController;
use App\Http\Controllers\Web\Admin\Modules\WorkLocationController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Admin'])->group(function (): void {
    Route::redirect('/', '/admin/dashboard')->name('home');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/application-parameters', [ApplicationParameterController::class, 'index'])->name('application-parameters');
    Route::post('/application-parameters/annual-leave-quota', [ApplicationParameterController::class, 'updateAnnualLeaveQuota'])->name('application-parameters.annual-leave-quota');

    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments');
    Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/{id}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
    Route::put('/departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    Route::get('/positions', [PositionController::class, 'index'])->name('positions');
    Route::get('/positions/create', [PositionController::class, 'create'])->name('positions.create');
    Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::get('/positions/{id}/edit', [PositionController::class, 'edit'])->name('positions.edit');
    Route::put('/positions/{id}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{id}', [PositionController::class, 'destroy'])->name('positions.destroy');

    Route::get('/work-locations', [WorkLocationController::class, 'index'])->name('work-locations');
    Route::get('/work-locations/create', [WorkLocationController::class, 'create'])->name('work-locations.create');
    Route::post('/work-locations', [WorkLocationController::class, 'store'])->name('work-locations.store');
    Route::get('/work-locations/{id}/edit', [WorkLocationController::class, 'edit'])->name('work-locations.edit');
    Route::put('/work-locations/{id}', [WorkLocationController::class, 'update'])->name('work-locations.update');
    Route::delete('/work-locations/{id}', [WorkLocationController::class, 'destroy'])->name('work-locations.destroy');

    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts');
    Route::get('/shifts/create', [ShiftController::class, 'create'])->name('shifts.create');
    Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::get('/shifts/{id}/edit', [ShiftController::class, 'edit'])->name('shifts.edit');
    Route::put('/shifts/{id}', [ShiftController::class, 'update'])->name('shifts.update');
    Route::delete('/shifts/{id}', [ShiftController::class, 'destroy'])->name('shifts.destroy');

    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays');
    Route::get('/holidays/create', [HolidayController::class, 'create'])->name('holidays.create');
    Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
    Route::get('/holidays/{id}/edit', [HolidayController::class, 'edit'])->name('holidays.edit');
    Route::put('/holidays/{id}', [HolidayController::class, 'update'])->name('holidays.update');
    Route::delete('/holidays/{id}', [HolidayController::class, 'destroy'])->name('holidays.destroy');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
});
