<?php

use App\Http\Controllers\Api\Internal\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('internal')
    ->name('api.internal.')
    ->middleware(['web', 'auth', 'signed'])
    ->group(function (): void {
        Route::get('/attachments/{attachmentUuid}/preview', [AttachmentController::class, 'show'])
            ->name('attachments.preview');
    });
