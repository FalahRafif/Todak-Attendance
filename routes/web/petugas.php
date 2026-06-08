<?php

use Illuminate\Support\Facades\Route;

Route::prefix('hrd')->name('hrd.')->middleware(['auth', 'role:HRD'])->group(function (): void {
    Route::redirect('/', '/hrd/dashboard')->name('home');
    Route::view('/dashboard', 'pages.admin.blank', ['title' => 'Dashboard HRD'])->name('dashboard');
});
