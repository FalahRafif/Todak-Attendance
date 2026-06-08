<?php

namespace App\Repositories\Eloquent;

use App\Models\BillingInstallment;
use App\Repositories\Contracts\BillingInstallmentRepositoryInterface;

class BillingInstallmentRepository extends BaseRepository implements BillingInstallmentRepositoryInterface
{
    public function __construct(BillingInstallment $model)
    {
        parent::__construct($model);
    }
}
