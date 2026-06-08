<?php

namespace App\Repositories\Eloquent;

use App\Models\EmployeeWorkLocation;
use App\Repositories\Contracts\EmployeeWorkLocationRepositoryInterface;

class EmployeeWorkLocationRepository extends BaseRepository implements EmployeeWorkLocationRepositoryInterface
{
    public function __construct(EmployeeWorkLocation $model)
    {
        parent::__construct($model);
    }
}
