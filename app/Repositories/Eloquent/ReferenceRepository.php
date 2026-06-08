<?php

namespace App\Repositories\Eloquent;

use App\Models\Reference;
use App\Repositories\Contracts\ReferenceRepositoryInterface;

class ReferenceRepository extends BaseRepository implements ReferenceRepositoryInterface
{
    public function __construct(Reference $model)
    {
        parent::__construct($model);
    }
}
