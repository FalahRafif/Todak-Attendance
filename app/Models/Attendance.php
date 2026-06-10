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
    'employee_id',
    'shift_id',
    'work_location_id',
    'attendance_date',
    'check_in_at',
    'check_in_photo_attachment_id',
    'check_in_latitude',
    'check_in_longitude',
    'check_in_distance_meter',
    'check_in_gps_accuracy_meter',
    'check_in_is_inside_radius',
    'check_in_work_mode_id',
    'check_in_note',
    'check_in_device_info',
    'check_out_at',
    'check_out_photo_attachment_id',
    'check_out_latitude',
    'check_out_longitude',
    'check_out_distance_meter',
    'check_out_gps_accuracy_meter',
    'check_out_is_inside_radius',
    'check_out_work_mode_id',
    'check_out_note',
    'check_out_device_info',
    'total_work_minutes',
    'late_minutes',
    'early_leave_minutes',
    'status_id',
    'is_need_approval',
    'approved_by',
    'approved_at',
    'approval_note',
    'created_at',
    'created_by',
    'updated_at',
    'updated_by',
    'deleted_at',
    'deleted_by',
    'delete_status',
])]
class Attendance extends Model
{
    use HasFactory, HasManualSoftDeletes;

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'check_in_is_inside_radius' => 'boolean',
            'check_out_is_inside_radius' => 'boolean',
            'total_work_minutes' => 'integer',
            'late_minutes' => 'integer',
            'early_leave_minutes' => 'integer',
            'is_need_approval' => 'boolean',
            'approved_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'delete_status' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'status_id');
    }

    public function checkInPhoto(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'check_in_photo_attachment_id');
    }

    public function checkOutPhoto(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'check_out_photo_attachment_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
