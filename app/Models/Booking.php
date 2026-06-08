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
    'customer_id',
    'package_id',
    'status_id',
    'location_id',
    'event_date',
    'event_session',
    'event_detail',
    'google_maps_pin',
    'reschedule_date',
    'reschedule_reason',
    'force_majeure_date',
    'force_majeure_reason',
    'operator_id',
    'created_at',
    'created_by',
    'updated_at',
    'updated_by',
    'deleted_at',
    'deleted_by',
    'delete_status',
])]
class Booking extends Model
{
    use HasFactory, HasManualSoftDeletes;

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'reschedule_date' => 'date',
            'force_majeure_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'delete_status' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'status_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function eventSession(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'event_session');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(BookingHistory::class, 'booking_id');
    }

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class, 'booking_id');
    }
}
