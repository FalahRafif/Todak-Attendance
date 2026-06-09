<?php

namespace App\Repositories\Eloquent;

use App\Models\AttendanceLog;
use App\Repositories\Contracts\AttendanceLogRepositoryInterface;

class AttendanceLogRepository extends BaseRepository implements AttendanceLogRepositoryInterface
{
    public function __construct(AttendanceLog $model)
    {
        parent::__construct($model);
    }
}
