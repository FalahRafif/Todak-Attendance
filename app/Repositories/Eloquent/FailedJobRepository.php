<?php

namespace App\Repositories\Eloquent;

use App\Models\FailedJob;
use App\Repositories\Contracts\FailedJobRepositoryInterface;

class FailedJobRepository extends BaseRepository implements FailedJobRepositoryInterface
{
    public function __construct(FailedJob $model)
    {
        parent::__construct($model);
    }
}
