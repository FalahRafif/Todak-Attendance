<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Admin'])->group(function (): void {
    Route::redirect('/', '/admin/dashboard')->name('home');
    Route::view('/dashboard', 'pages.admin.blank', ['title' => 'Dashboard'])->name('dashboard');
});
