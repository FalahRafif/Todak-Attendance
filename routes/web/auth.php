<?php

use App\Http\Controllers\Web\Auth\AuthController as WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
