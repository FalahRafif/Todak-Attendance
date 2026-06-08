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
    'booking_id',
    'total_amount',
    'total_paid',
    'refunded_amount',
    'status_id',
    'created_at',
    'created_by',
    'updated_at',
    'updated_by',
    'deleted_at',
    'deleted_by',
    'delete_status',
])]
class Billing extends Model
{
    use HasFactory, HasManualSoftDeletes;

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'total_paid' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'delete_status' => 'boolean',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'status_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(BillingDetail::class, 'billing_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(BillingInstallment::class, 'billing_id');
    }
}
