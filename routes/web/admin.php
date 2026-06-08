<?php

use App\Http\Controllers\Web\Admin\AdminPreviewController;
use App\Http\Controllers\Web\Admin\BlankController;
use App\Http\Controllers\Web\Admin\LocationPricingRuleController;
use App\Http\Controllers\Web\Admin\PackageController;
use App\Http\Controllers\Web\Admin\DpPercentageRuleController;
use App\Http\Controllers\Web\Admin\PackageDateRuleController;
use App\Http\Controllers\Web\Admin\PaymentDateRuleController;
use App\Http\Controllers\Web\Admin\ProfileController;
use App\Http\Controllers\Web\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Admin'])->group(function () {
    Route::redirect('/', '/admin/dashboard')->name('home');
    Route::get('/dashboard', [AdminPreviewController::class, 'dashboard'])->name('dashboard');
    Route::get('/bookings', [AdminPreviewController::class, 'bookingsList'])->name('bookings.list');
    Route::get('/bookings/{booking}', [AdminPreviewController::class, 'bookingDetail'])->name('bookings.detail');
    Route::get('/calendar', [AdminPreviewController::class, 'calendar'])->name('calendar');
    Route::get('/calendar/events', [AdminPreviewController::class, 'calendarEvents'])->name('calendar.events');
    Route::get('/packages', [PackageController::class, 'index'])->name('packages');
    Route::get('/packages/create', [PackageController::class, 'create'])->name('packages.create');
    Route::get('/packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit');
    Route::get('/location-rules', [LocationPricingRuleController::class, 'index'])->name('location.rules');
    Route::get('/location-rules/create', [LocationPricingRuleController::class, 'create'])->name('location.rules.create');
    Route::get('/location-rules/{locationPricingRule}/edit', [LocationPricingRuleController::class, 'edit'])->name('location.rules.edit');
    Route::get('/payment-date-rules', [PaymentDateRuleController::class, 'index'])->name('payment-date-rules');
    Route::get('/payment-date-rules/create', [PaymentDateRuleController::class, 'create'])->name('payment-date-rules.create');
    Route::get('/payment-date-rules/{setting}/edit', [PaymentDateRuleController::class, 'edit'])->name('payment-date-rules.edit');
    Route::get('/dp-percentage-rules', [DpPercentageRuleController::class, 'index'])->name('dp-percentage-rules');
    Route::get('/dp-percentage-rules/create', [DpPercentageRuleController::class, 'create'])->name('dp-percentage-rules.create');
    Route::get('/dp-percentage-rules/{setting}/edit', [DpPercentageRuleController::class, 'edit'])->name('dp-percentage-rules.edit');
    Route::get('/package-date-rules', [PackageDateRuleController::class, 'index'])->name('package-date-rules');
    Route::get('/package-date-rules/create', [PackageDateRuleController::class, 'create'])->name('package-date-rules.create');
    Route::get('/package-date-rules/{setting}/edit', [PackageDateRuleController::class, 'edit'])->name('package-date-rules.edit');
    Route::get('/customers', [AdminPreviewController::class, 'customers'])->name('customers');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/settings', [AdminPreviewController::class, 'settings'])->name('settings');
    Route::get('/blank', [BlankController::class, 'index'])->name('blank');
});
