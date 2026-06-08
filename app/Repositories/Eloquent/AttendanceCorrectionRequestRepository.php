<?php

namespace App\Repositories\Eloquent;

use App\Models\AttendanceCorrectionRequest;
use App\Repositories\Contracts\AttendanceCorrectionRequestRepositoryInterface;

class AttendanceCorrectionRequestRepository extends BaseRepository implements AttendanceCorrectionRequestRepositoryInterface
{
    public function __construct(AttendanceCorrectionRequest $model)
    {
        parent::__construct($model);
    }
}
