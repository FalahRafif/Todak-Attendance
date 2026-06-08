<?php

namespace App\Repositories\Eloquent;

use App\Models\Approval;
use App\Repositories\Contracts\ApprovalRepositoryInterface;

class ApprovalRepository extends BaseRepository implements ApprovalRepositoryInterface
{
    public function __construct(Approval $model)
    {
        parent::__construct($model);
    }
}
