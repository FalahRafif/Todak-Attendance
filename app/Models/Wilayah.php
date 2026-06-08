<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'kode',
    'nama',
])]
class Wilayah extends Model
{
    use HasFactory;

    protected $table = 'wilayah';

    protected $primaryKey = 'kode';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'wilayah_id', 'kode');
    }
}
