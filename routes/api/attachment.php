<?php

use App\Http\Controllers\Api\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('internal/attachments')->name('api.internal.attachments.')->group(function (): void {
    Route::get('/{attachmentUuid}/preview', [AttachmentController::class, 'preview'])->name('preview')->middleware('signed');
});
