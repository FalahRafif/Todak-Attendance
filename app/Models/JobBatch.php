<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'id',
    'name',
    'total_jobs',
    'pending_jobs',
    'failed_jobs',
    'failed_job_ids',
    'options',
    'cancelled_at',
    'created_at',
    'finished_at',
])]
class JobBatch extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'total_jobs' => 'integer',
            'pending_jobs' => 'integer',
            'failed_jobs' => 'integer',
            'cancelled_at' => 'integer',
            'created_at' => 'integer',
            'finished_at' => 'integer',
        ];
    }
}
