<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveBalance;
use App\Repositories\Contracts\LeaveBalanceRepositoryInterface;

class LeaveBalanceRepository extends BaseRepository implements LeaveBalanceRepositoryInterface
{
    public function __construct(LeaveBalance $model)
    {
        parent::__construct($model);
    }
}
