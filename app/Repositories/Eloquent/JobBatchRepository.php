<?php

namespace App\Repositories\Eloquent;

use App\Models\JobBatch;
use App\Repositories\Contracts\JobBatchRepositoryInterface;

class JobBatchRepository extends BaseRepository implements JobBatchRepositoryInterface
{
    public function __construct(JobBatch $model)
    {
        parent::__construct($model);
    }
}
