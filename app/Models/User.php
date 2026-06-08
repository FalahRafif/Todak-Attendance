<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasManualSoftDeletes;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'uuid',
    'name',
    'username',
    'email',
    'email_verified_at',
    'password',
    'remember_token',
    'role_id',
    'profile_image_attachment_id',
    'created_by',
    'updated_by',
    'deleted_at',
    'deleted_by',
    'delete_status',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasManualSoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
            'delete_status' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function profileImageAttachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'profile_image_attachment_id');
    }

    public function bookingsAsOperator(): HasMany
    {
        return $this->hasMany(Booking::class, 'operator_id');
    }

    public function bookingHistoriesAsOperator(): HasMany
    {
        return $this->hasMany(BookingHistory::class, 'operator_id');
    }

    public function roleName(): ?string
    {
        if ($this->relationLoaded('role')) {
            return $this->role?->name;
        }

        return $this->role()->value('name');
    }

    public function hasRole(string|array $roles): bool
    {
        $roleName = $this->roleName();

        if ($roleName === null) {
            return false;
        }

        $acceptedRoles = is_array($roles) ? $roles : [$roles];

        return in_array($roleName, $acceptedRoles, true);
    }
}
