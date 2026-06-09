<?php

namespace App\Repositories\Eloquent;

use App\Models\WorkLocation;
use App\Repositories\Contracts\WorkLocationRepositoryInterface;

class WorkLocationRepository extends BaseRepository implements WorkLocationRepositoryInterface
{
    public function __construct(WorkLocation $model)
    {
        parent::__construct($model);
    }
}
