<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_schedules', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('schedule_date');
            $table->boolean('is_day_off')->default(false);
            $table->text('note')->nullable();
            $this->auditColumns($table);
            $table->unique(['employee_id', 'schedule_date'], 'employee_schedules_employee_date_unique');
            $table->index(['shift_id', 'delete_status']);
            $table->index(['schedule_date', 'delete_status']);
        });

        Schema::create('holidays', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->date('holiday_date');
            $table->text('description')->nullable();
            $table->boolean('is_national_holiday')->default(false);
            $table->boolean('is_company_holiday')->default(false);
            $this->auditColumns($table);
            $table->index(['holiday_date', 'delete_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('employee_schedules');
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
