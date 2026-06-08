<?php

namespace App\Models;

use App\Models\Concerns\HasManualSoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'uuid',
    'name',
    'created_at',
    'updated_at',
    'updated_by',
    'deleted_at',
    'deleted_by',
    'delete_status',
])]
class Role extends Model
{
    use HasFactory, HasManualSoftDeletes;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'delete_status' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
