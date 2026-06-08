<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'queue',
    'payload',
    'attempts',
    'reserved_at',
    'available_at',
    'created_at',
])]
class Job extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'reserved_at' => 'integer',
            'available_at' => 'integer',
            'created_at' => 'integer',
        ];
    }
}
