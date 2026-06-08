<?php

namespace App\Repositories\Eloquent;

use App\Models\Billing;
use App\Repositories\Contracts\BillingRepositoryInterface;

class BillingRepository extends BaseRepository implements BillingRepositoryInterface
{
    public function __construct(Billing $model)
    {
        parent::__construct($model);
    }
}
