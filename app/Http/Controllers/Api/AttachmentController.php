<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Services\AttachmentSecurityService;
use Illuminate\Http\Response;

class AttachmentController extends Controller
{
    public function __construct(private AttachmentSecurityService $attachmentSecurityService)
    {
    }

    public function preview(string $attachmentUuid): Response
    {
        $attachment = Attachment::query()->where('uuid', $attachmentUuid)->firstOrFail();

        return $this->attachmentSecurityService->buildInlineImageResponse($attachment);
    }
}
