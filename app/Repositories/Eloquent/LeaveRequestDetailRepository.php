<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveRequestDetail;
use App\Repositories\Contracts\LeaveRequestDetailRepositoryInterface;

class LeaveRequestDetailRepository extends BaseRepository implements LeaveRequestDetailRepositoryInterface
{
    public function __construct(LeaveRequestDetail $model)
    {
        parent::__construct($model);
    }
}
