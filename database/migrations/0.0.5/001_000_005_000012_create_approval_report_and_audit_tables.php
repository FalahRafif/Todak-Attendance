<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('status_id')->constrained('references')->restrictOnDelete();
            $table->unsignedInteger('approval_level')->default(1);
            $table->text('note')->nullable();
            $table->dateTime('approved_at')->nullable();
            $this->auditColumns($table);
            $table->index(['approvable_type', 'approvable_id']);
            $table->index(['status_id', 'delete_status']);
        });

        Schema::create('attendance_monthly_summaries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('total_work_days')->default(0);
            $table->unsignedInteger('total_present')->default(0);
            $table->unsignedInteger('total_late')->default(0);
            $table->unsignedInteger('total_absent')->default(0);
            $table->unsignedInteger('total_sick')->default(0);
            $table->unsignedInteger('total_leave')->default(0);
            $table->unsignedInteger('total_permission')->default(0);
            $table->unsignedInteger('total_incomplete')->default(0);
            $table->unsignedInteger('total_outside_radius')->default(0);
            $table->unsignedInteger('total_work_minutes')->default(0);
            $table->unsignedInteger('total_late_minutes')->default(0);
            $table->unsignedInteger('total_early_leave_minutes')->default(0);
            $table->dateTime('generated_at')->nullable();
            $this->auditColumns($table);
            $table->unique(['employee_id', 'year', 'month'], 'attendance_monthly_employee_period_unique');
            $table->index(['year', 'month']);
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('module', 100);
            $table->string('action', 100);
            $table->text('description')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $this->auditColumns($table);
            $table->index(['user_id', 'delete_status']);
            $table->index(['module', 'delete_status']);
            $table->index(['action', 'delete_status']);
            $table->index(['created_at', 'delete_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('attendance_monthly_summaries');
        Schema::dropIfExists('approvals');
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
