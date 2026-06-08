<?php

use App\Http\Controllers\Api\Auth\AuthController as ApiAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'guest'])->group(function () {
    Route::post('/login', [ApiAuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [ApiAuthController::class, 'logout'])
    ->name('logout')
    ->middleware(['web', 'auth']);
