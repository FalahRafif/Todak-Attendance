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
    'name',
    'path',
    'type_file',
    'created_at',
    'created_by',
    'updated_at',
    'updated_by',
    'deleted_at',
    'deleted_by',
    'delete_status',
])]
class Attachment extends Model
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

    public function typeFile(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'type_file');
    }

    public function packagesAsThumbnail(): HasMany
    {
        return $this->hasMany(Package::class, 'thumbnail_attachment_id');
    }

    public function usersAsProfileImage(): HasMany
    {
        return $this->hasMany(User::class, 'profile_image_attachment_id');
    }

    public function paymentsAsTransferReceipt(): HasMany
    {
        return $this->hasMany(Payment::class, 'transfer_receipt_attachment_id');
    }
}
