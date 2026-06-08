<?php

namespace App\Repositories\Eloquent;

use App\Models\BookingHistory;
use App\Repositories\Contracts\BookingHistoryRepositoryInterface;

class BookingHistoryRepository extends BaseRepository implements BookingHistoryRepositoryInterface
{
    public function __construct(BookingHistory $model)
    {
        parent::__construct($model);
    }
}
