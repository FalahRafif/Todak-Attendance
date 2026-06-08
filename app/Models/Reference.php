<?php

namespace App\Models;

use App\Models\Concerns\HasManualSoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'uuid',
    'code',
    'description',
    'group_id',
    'created_at',
    'created_by',
    'updated_at',
    'updated_by',
    'deleted_at',
    'deleted_by',
    'delete_status',
])]
class Reference extends Model
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

    public function locationLevels(): HasMany
    {
        return $this->hasMany(Location::class, 'level_id');
    }

    public function locationPricingRulePriceTypes(): HasMany
    {
        return $this->hasMany(LocationPricingRule::class, 'price_type');
    }

    public function attachmentTypes(): HasMany
    {
        return $this->hasMany(Attachment::class, 'type_file');
    }

    public function packageStatuses(): HasMany
    {
        return $this->hasMany(Package::class, 'status_id');
    }

    public function packageTypes(): HasMany
    {
        return $this->hasMany(Package::class, 'package_type');
    }

    public function settingTypes(): HasMany
    {
        return $this->hasMany(Setting::class, 'type_id');
    }

    public function bookingStatuses(): HasMany
    {
        return $this->hasMany(Booking::class, 'status_id');
    }

    public function bookingEventSessions(): HasMany
    {
        return $this->hasMany(Booking::class, 'event_session');
    }

    public function bookingHistoryStatuses(): HasMany
    {
        return $this->hasMany(BookingHistory::class, 'status_id');
    }

    public function billingStatuses(): HasMany
    {
        return $this->hasMany(Billing::class, 'status_id');
    }

    public function billingDetailTypes(): HasMany
    {
        return $this->hasMany(BillingDetail::class, 'billing_type');
    }

    public function billingInstallmentTypes(): HasMany
    {
        return $this->hasMany(BillingInstallment::class, 'installment_type');
    }

    public function billingInstallmentStatuses(): HasMany
    {
        return $this->hasMany(BillingInstallment::class, 'status_id');
    }

    public function paymentTypes(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_type');
    }

    public function paymentStatuses(): HasMany
    {
        return $this->hasMany(Payment::class, 'status_id');
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_method');
    }
}
