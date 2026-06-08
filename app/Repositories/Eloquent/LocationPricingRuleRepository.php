<?php

namespace App\Repositories\Eloquent;

use App\Models\LocationPricingRule;
use App\Repositories\Contracts\LocationPricingRuleRepositoryInterface;

class LocationPricingRuleRepository extends BaseRepository implements LocationPricingRuleRepositoryInterface
{
    public function __construct(LocationPricingRule $model)
    {
        parent::__construct($model);
    }
}
