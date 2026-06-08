<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('references')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total_days')->default(1);
            $table->text('reason')->nullable();
            $table->foreignId('attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->foreignId('status_id')->constrained('references')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_note')->nullable();
            $table->text('rejected_reason')->nullable();
            $this->auditColumns($table);
            $table->index(['employee_id', 'delete_status']);
            $table->index(['leave_type_id', 'delete_status']);
            $table->index(['status_id', 'delete_status']);
            $table->index(['start_date', 'delete_status']);
            $table->index(['end_date', 'delete_status']);
        });

        Schema::create('leave_request_details', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->cascadeOnDelete();
            $table->date('leave_date');
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $this->auditColumns($table);
            $table->index(['leave_request_id', 'delete_status']);
            $table->index(['leave_date', 'delete_status']);
            $table->index(['attendance_id', 'delete_status']);
        });

        Schema::create('leave_balances', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->nullable()->constrained('references')->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('total_quota')->default(0);
            $table->unsignedInteger('used_quota')->default(0);
            $table->unsignedInteger('remaining_quota')->default(0);
            $this->auditColumns($table);
            $table->index('employee_id', 'leave_balances_employee_id_index');
            $table->index('leave_type_id', 'leave_balances_leave_type_id_index');
            $table->unique(['employee_id', 'leave_type_id', 'year'], 'leave_balances_employee_leave_type_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_request_details');
        Schema::dropIfExists('leave_requests');
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
