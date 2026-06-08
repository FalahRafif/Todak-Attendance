<?php

namespace App\Models;

use App\Models\Concerns\HasManualSoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid',
    'attendance_id',
    'employee_id',
    'action_type_id',
    'action_at',
    'latitude',
    'longitude',
    'distance_meter',
    'gps_accuracy_meter',
    'is_inside_radius',
    'work_mode_id',
    'note',
    'photo_attachment_id',
    'device_info',
    'ip_address',
    'user_agent',
    'source',
    'created_at',
    'created_by',
    'updated_at',
    'updated_by',
    'deleted_at',
    'deleted_by',
    'delete_status',
])]
class AttendanceLog extends Model
{
    use HasFactory, HasManualSoftDeletes;

    protected function casts(): array
    {
        return [
            'action_at' => 'datetime',
            'is_inside_radius' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'delete_status' => 'boolean',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function actionType(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'action_type_id');
    }
}
