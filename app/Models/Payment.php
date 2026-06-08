<?php

namespace App\Models;

use App\Models\Concerns\HasManualSoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid',
    'billing_installment_id',
    'payment_type',
    'status_id',
    'payment_method',
    'amount',
    'paid_at',
    'transfer_receipt_attachment_id',
    'rejection_reason',
    'created_at',
    'created_by',
    'updated_at',
    'updated_by',
    'deleted_at',
    'deleted_by',
    'delete_status',
])]
class Payment extends Model
{
    use HasFactory, HasManualSoftDeletes;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'delete_status' => 'boolean',
        ];
    }

    public function billingInstallment(): BelongsTo
    {
        return $this->belongsTo(BillingInstallment::class, 'billing_installment_id');
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'payment_type');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'status_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'payment_method');
    }

    public function transferReceiptAttachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'transfer_receipt_attachment_id');
    }
}
