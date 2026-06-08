<?php

namespace App\Repositories\Eloquent;

use App\Models\Cache;
use App\Repositories\Contracts\CacheRepositoryInterface;

class CacheRepository extends BaseRepository implements CacheRepositoryInterface
{
    public function __construct(Cache $model)
    {
        parent::__construct($model);
    }
}
