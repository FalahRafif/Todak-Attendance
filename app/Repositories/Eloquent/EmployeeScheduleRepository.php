<?php

namespace App\Repositories\Eloquent;

use App\Models\EmployeeSchedule;
use App\Repositories\Contracts\EmployeeScheduleRepositoryInterface;

class EmployeeScheduleRepository extends BaseRepository implements EmployeeScheduleRepositoryInterface
{
    public function __construct(EmployeeSchedule $model)
    {
        parent::__construct($model);
    }
}
