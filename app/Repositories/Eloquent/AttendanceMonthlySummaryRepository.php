<?php

namespace App\Repositories\Eloquent;

use App\Models\AttendanceMonthlySummary;
use App\Repositories\Contracts\AttendanceMonthlySummaryRepositoryInterface;

class AttendanceMonthlySummaryRepository extends BaseRepository implements AttendanceMonthlySummaryRepositoryInterface
{
    public function __construct(AttendanceMonthlySummary $model)
    {
        parent::__construct($model);
    }
}
