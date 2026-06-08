<?php

use App\Http\Controllers\Web\Public\BookingSupportController;
use App\Http\Controllers\Web\Public\LandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/packages', [LandingPageController::class, 'packages'])->name('packages.page');
Route::get('/tentang-kami/etherno', [LandingPageController::class, 'aboutEtherno'])->name('about.etherno');
Route::get('/booking', [LandingPageController::class, 'booking'])->name('booking.page');
Route::redirect('/booking/alur-proses', '/booking')->name('booking.flow');
Route::get('/booking/success', [BookingSupportController::class, 'success'])->name('booking.success');
Route::get('/booking/proof/{bookingUuid}', [BookingSupportController::class, 'downloadSubmissionProof'])
    ->middleware('signed')
    ->name('booking.proof.download');
Route::get('/booking/status', [BookingSupportController::class, 'status'])->name('booking.status');
Route::get('/booking/reschedule', [BookingSupportController::class, 'reschedule'])->name('booking.reschedule');
Route::get('/booking/cancellation-policy', [BookingSupportController::class, 'cancellationPolicy'])->name('booking.cancellation.policy');
