<?php

use App\Http\Controllers\Web\Admin\AdminModuleController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Admin'])->group(function (): void {
    Route::redirect('/', '/admin/dashboard')->name('home');
    Route::get('/dashboard', [AdminModuleController::class, 'dashboard'])->name('dashboard');

    Route::get('/departments', [AdminModuleController::class, 'departments'])->name('departments');
    Route::get('/departments/create', [AdminModuleController::class, 'createDepartment'])->name('departments.create');
    Route::post('/departments', [AdminModuleController::class, 'storeDepartment'])->name('departments.store');
    Route::get('/departments/{id}/edit', [AdminModuleController::class, 'editDepartment'])->name('departments.edit');
    Route::put('/departments/{id}', [AdminModuleController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{id}', [AdminModuleController::class, 'deleteDepartment'])->name('departments.destroy');

    Route::get('/positions', [AdminModuleController::class, 'positions'])->name('positions');
    Route::get('/positions/create', [AdminModuleController::class, 'createPosition'])->name('positions.create');
    Route::post('/positions', [AdminModuleController::class, 'storePosition'])->name('positions.store');
    Route::get('/positions/{id}/edit', [AdminModuleController::class, 'editPosition'])->name('positions.edit');
    Route::put('/positions/{id}', [AdminModuleController::class, 'updatePosition'])->name('positions.update');
    Route::delete('/positions/{id}', [AdminModuleController::class, 'deletePosition'])->name('positions.destroy');

    Route::get('/work-locations', [AdminModuleController::class, 'workLocations'])->name('work-locations');
    Route::get('/work-locations/create', [AdminModuleController::class, 'createWorkLocation'])->name('work-locations.create');
    Route::post('/work-locations', [AdminModuleController::class, 'storeWorkLocation'])->name('work-locations.store');
    Route::get('/work-locations/{id}/edit', [AdminModuleController::class, 'editWorkLocation'])->name('work-locations.edit');
    Route::put('/work-locations/{id}', [AdminModuleController::class, 'updateWorkLocation'])->name('work-locations.update');
    Route::delete('/work-locations/{id}', [AdminModuleController::class, 'deleteWorkLocation'])->name('work-locations.destroy');

    Route::get('/shifts', [AdminModuleController::class, 'shifts'])->name('shifts');
    Route::get('/shifts/create', [AdminModuleController::class, 'createShift'])->name('shifts.create');
    Route::post('/shifts', [AdminModuleController::class, 'storeShift'])->name('shifts.store');
    Route::get('/shifts/{id}/edit', [AdminModuleController::class, 'editShift'])->name('shifts.edit');
    Route::put('/shifts/{id}', [AdminModuleController::class, 'updateShift'])->name('shifts.update');
    Route::delete('/shifts/{id}', [AdminModuleController::class, 'deleteShift'])->name('shifts.destroy');

    Route::get('/holidays', [AdminModuleController::class, 'holidays'])->name('holidays');
    Route::get('/holidays/create', [AdminModuleController::class, 'createHoliday'])->name('holidays.create');
    Route::post('/holidays', [AdminModuleController::class, 'storeHoliday'])->name('holidays.store');
    Route::get('/holidays/{id}/edit', [AdminModuleController::class, 'editHoliday'])->name('holidays.edit');
    Route::put('/holidays/{id}', [AdminModuleController::class, 'updateHoliday'])->name('holidays.update');
    Route::delete('/holidays/{id}', [AdminModuleController::class, 'deleteHoliday'])->name('holidays.destroy');

    Route::get('/employees', [AdminModuleController::class, 'employees'])->name('employees');
    Route::get('/employees/create', [AdminModuleController::class, 'createEmployee'])->name('employees.create');
    Route::post('/employees', [AdminModuleController::class, 'storeEmployee'])->name('employees.store');
    Route::get('/employees/{id}/edit', [AdminModuleController::class, 'editEmployee'])->name('employees.edit');
    Route::put('/employees/{id}', [AdminModuleController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{id}', [AdminModuleController::class, 'deleteEmployee'])->name('employees.destroy');
});
