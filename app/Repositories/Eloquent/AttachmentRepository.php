<?php

namespace App\Repositories\Eloquent;

use App\Models\Attachment;
use App\Repositories\Contracts\AttachmentRepositoryInterface;

class AttachmentRepository extends BaseRepository implements AttachmentRepositoryInterface
{
    public function __construct(Attachment $model)
    {
        parent::__construct($model);
    }
}
