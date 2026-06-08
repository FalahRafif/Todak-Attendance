<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->restrictOnDelete();
            $table->foreignId('work_location_id')->constrained('work_locations')->restrictOnDelete();
            $table->date('attendance_date');
            $table->dateTime('check_in_at')->nullable();
            $table->foreignId('check_in_photo_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->decimal('check_in_distance_meter', 10, 2)->nullable();
            $table->boolean('check_in_is_inside_radius')->nullable();
            $table->foreignId('check_in_work_mode_id')->nullable()->constrained('references')->nullOnDelete();
            $table->text('check_in_note')->nullable();
            $table->text('check_in_device_info')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->foreignId('check_out_photo_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->decimal('check_out_distance_meter', 10, 2)->nullable();
            $table->boolean('check_out_is_inside_radius')->nullable();
            $table->foreignId('check_out_work_mode_id')->nullable()->constrained('references')->nullOnDelete();
            $table->text('check_out_note')->nullable();
            $table->text('check_out_device_info')->nullable();
            $table->unsignedInteger('total_work_minutes')->default(0);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('early_leave_minutes')->default(0);
            $table->foreignId('status_id')->constrained('references')->restrictOnDelete();
            $table->boolean('is_need_approval')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_note')->nullable();
            $this->auditColumns($table);
            $table->unique(['employee_id', 'attendance_date'], 'attendances_employee_date_unique');
            $table->index(['attendance_date', 'delete_status']);
            $table->index(['status_id', 'delete_status']);
            $table->index(['work_location_id', 'delete_status']);
            $table->index(['is_need_approval', 'delete_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }

    private function auditColumns(Blueprint $table): void
    {
        $table->timestamp('created_at')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamp('updated_at')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamp('deleted_at')->nullable();
        $table->unsignedBigInteger('deleted_by')->nullable();
        $table->boolean('delete_status')->default(false);
        $table->index('delete_status');
    }
};
