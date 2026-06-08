<?php

use App\Http\Controllers\Api\Admin\BookingDetailController;
use App\Http\Controllers\Api\Admin\LocationPricingRuleController;
use App\Http\Controllers\Api\Admin\PackageController;
use App\Http\Controllers\Api\Admin\DpPercentageRuleController;
use App\Http\Controllers\Api\Admin\PackageDateRuleController;
use App\Http\Controllers\Api\Admin\PaymentDateRuleController;
use App\Http\Controllers\Api\Admin\ProfileController;
use App\Http\Controllers\Api\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['web', 'auth', 'role:Admin'])
    ->name('api.admin.')
    ->group(function (): void {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

        Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
        Route::put('/packages/{package}', [PackageController::class, 'update'])->name('packages.update');
        Route::delete('/packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');

        Route::post('/location-rules', [LocationPricingRuleController::class, 'store'])->name('location-rules.store');
        Route::put('/location-rules/{locationPricingRule}', [LocationPricingRuleController::class, 'update'])->name('location-rules.update');
        Route::delete('/location-rules/{locationPricingRule}', [LocationPricingRuleController::class, 'destroy'])->name('location-rules.destroy');
        Route::get('/location-rules/options', [LocationPricingRuleController::class, 'locationOptions'])->name('location-rules.options');

        Route::post('/payment-date-rules', [PaymentDateRuleController::class, 'store'])->name('payment-date-rules.store');
        Route::put('/payment-date-rules/{setting}', [PaymentDateRuleController::class, 'update'])->name('payment-date-rules.update');
        Route::delete('/payment-date-rules/{setting}', [PaymentDateRuleController::class, 'destroy'])->name('payment-date-rules.destroy');

        Route::post('/dp-percentage-rules', [DpPercentageRuleController::class, 'store'])->name('dp-percentage-rules.store');
        Route::put('/dp-percentage-rules/{setting}', [DpPercentageRuleController::class, 'update'])->name('dp-percentage-rules.update');
        Route::delete('/dp-percentage-rules/{setting}', [DpPercentageRuleController::class, 'destroy'])->name('dp-percentage-rules.destroy');

        Route::post('/package-date-rules', [PackageDateRuleController::class, 'store'])->name('package-date-rules.store');
        Route::put('/package-date-rules/{setting}', [PackageDateRuleController::class, 'update'])->name('package-date-rules.update');
        Route::delete('/package-date-rules/{setting}', [PackageDateRuleController::class, 'destroy'])->name('package-date-rules.destroy');

        Route::post('/bookings/{booking}/approve', [BookingDetailController::class, 'approve'])->name('bookings.approve');
        Route::post('/bookings/{booking}/reject', [BookingDetailController::class, 'reject'])->name('bookings.reject');
        Route::post('/bookings/{booking}/upload-payment', [BookingDetailController::class, 'uploadPayment'])->name('bookings.upload-payment');
        Route::post('/bookings/{booking}/verify-dp', [BookingDetailController::class, 'verifyDp'])->name('bookings.verify-dp');
        Route::post('/bookings/{booking}/reject-manual', [BookingDetailController::class, 'rejectManual'])->name('bookings.reject-manual');
        Route::post('/bookings/{booking}/verify-final', [BookingDetailController::class, 'verifyFinalPayment'])->name('bookings.verify-final');
        Route::post('/bookings/{booking}/payments/{payment}/approve', [BookingDetailController::class, 'approvePayment'])->name('bookings.payments.approve');
        Route::post('/bookings/{booking}/payments/{payment}/reject', [BookingDetailController::class, 'rejectPayment'])->name('bookings.payments.reject');
        Route::post('/bookings/{booking}/cancel', [BookingDetailController::class, 'cancelBooking'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/complete', [BookingDetailController::class, 'completeBooking'])->name('bookings.complete');
        Route::post('/bookings/{booking}/force-majeure', [BookingDetailController::class, 'forceMajeure'])->name('bookings.force-majeure');
        Route::post('/bookings/{booking}/upload-refund-proof', [BookingDetailController::class, 'uploadRefundProof'])->name('bookings.upload-refund-proof');
        Route::post('/bookings/{booking}/billing-details', [BookingDetailController::class, 'storeBillingDetail'])->name('bookings.billing-details.store');
        Route::post('/bookings/{booking}/installments', [BookingDetailController::class, 'storeInstallment'])->name('bookings.installments.store');
    });

Route::prefix('admin')
    ->middleware(['web', 'auth'])
    ->name('api.admin.')
    ->group(function (): void {
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
