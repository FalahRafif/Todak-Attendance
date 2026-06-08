<?php

namespace App\Repositories\Eloquent;

use App\Models\Job;
use App\Repositories\Contracts\JobRepositoryInterface;

class JobRepository extends BaseRepository implements JobRepositoryInterface
{
    public function __construct(Job $model)
    {
        parent::__construct($model);
    }
}
