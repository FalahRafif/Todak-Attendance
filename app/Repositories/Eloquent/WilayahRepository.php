<?php

namespace App\Repositories\Eloquent;

use App\Models\Wilayah;
use App\Repositories\Contracts\WilayahRepositoryInterface;

class WilayahRepository extends BaseRepository implements WilayahRepositoryInterface
{
    public function __construct(Wilayah $model)
    {
        parent::__construct($model);
    }
}
