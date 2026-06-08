<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['web', 'auth', 'role:admin'])
    ->name('api.admin.')
    ->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
