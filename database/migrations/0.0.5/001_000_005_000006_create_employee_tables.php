<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_number')->unique();
            $table->string('full_name');
            $table->string('phone', 30)->nullable();
            $table->string('gender', 20)->nullable();
            $table->foreignId('employee_type_id')->nullable()->constrained('references')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('work_location_id')->nullable()->constrained('work_locations')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('join_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $this->auditColumns($table);
            $table->index(['user_id', 'delete_status']);
            $table->index(['employee_type_id', 'delete_status']);
            $table->index(['department_id', 'delete_status']);
            $table->index(['position_id', 'delete_status']);
            $table->index(['work_location_id', 'delete_status']);
            $table->index(['shift_id', 'delete_status']);
            $table->index(['is_active', 'delete_status']);
        });

        Schema::create('employee_work_locations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('work_location_id')->constrained('work_locations')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $this->auditColumns($table);
            $table->unique(['employee_id', 'work_location_id'], 'ewl_employee_work_location_unique');
            $table->index(['work_location_id', 'delete_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_work_locations');
        Schema::dropIfExists('employees');
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
