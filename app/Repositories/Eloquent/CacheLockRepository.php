<?php

namespace App\Repositories\Eloquent;

use App\Models\CacheLock;
use App\Repositories\Contracts\CacheLockRepositoryInterface;

class CacheLockRepository extends BaseRepository implements CacheLockRepositoryInterface
{
    public function __construct(CacheLock $model)
    {
        parent::__construct($model);
    }
}
