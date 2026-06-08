<?php

namespace App\Repositories\Eloquent;

use App\Models\BillingDetail;
use App\Repositories\Contracts\BillingDetailRepositoryInterface;

class BillingDetailRepository extends BaseRepository implements BillingDetailRepositoryInterface
{
    public function __construct(BillingDetail $model)
    {
        parent::__construct($model);
    }
}
