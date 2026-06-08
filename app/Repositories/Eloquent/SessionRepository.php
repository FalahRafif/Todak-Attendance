<?php

namespace App\Repositories\Eloquent;

use App\Models\Session;
use App\Repositories\Contracts\SessionRepositoryInterface;

class SessionRepository extends BaseRepository implements SessionRepositoryInterface
{
    public function __construct(Session $model)
    {
        parent::__construct($model);
    }
}
