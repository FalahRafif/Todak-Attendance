<?php

namespace App\Models;

use App\Models\Concerns\HasManualSoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'uuid',
    'case_id',
    'name',
    'address',
    'description',
    'price',
    'status_id',
    'thumbnail_attachment_id',
    'package_type',
    'created_at',
    'created_by',
    'updated_at',
    'updated_by',
    'deleted_at',
    'deleted_by',
    'delete_status',
])]
class Package extends Model
{
    use HasFactory, HasManualSoftDeletes;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'delete_status' => 'boolean',
        ];
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'status_id');
    }

    public function thumbnailAttachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'thumbnail_attachment_id');
    }

    public function packageType(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'package_type');
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(PackageBenefit::class, 'package_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'package_id');
    }
}
