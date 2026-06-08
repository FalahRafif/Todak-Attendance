<?php

namespace App\Repositories\Eloquent;

use App\Models\PackageBenefit;
use App\Repositories\Contracts\PackageBenefitRepositoryInterface;

class PackageBenefitRepository extends BaseRepository implements PackageBenefitRepositoryInterface
{
    public function __construct(PackageBenefit $model)
    {
        parent::__construct($model);
    }
}
