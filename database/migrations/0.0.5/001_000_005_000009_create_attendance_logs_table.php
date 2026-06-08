<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('action_type_id')->constrained('references')->restrictOnDelete();
            $table->dateTime('action_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('distance_meter', 10, 2)->nullable();
            $table->boolean('is_inside_radius')->nullable();
            $table->foreignId('work_mode_id')->nullable()->constrained('references')->nullOnDelete();
            $table->text('note')->nullable();
            $table->foreignId('photo_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->text('device_info')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $this->auditColumns($table);
            $table->index(['attendance_id', 'delete_status']);
            $table->index(['employee_id', 'delete_status']);
            $table->index(['action_at', 'delete_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
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
