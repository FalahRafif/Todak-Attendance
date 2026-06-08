<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_locations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('radius_meter')->default(100);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $this->auditColumns($table);
            $table->index(['is_default', 'delete_status']);
            $table->index(['is_active', 'delete_status']);
        });

        Schema::create('shifts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->time('check_in_start_time')->nullable();
            $table->time('check_in_end_time')->nullable();
            $table->time('check_out_start_time')->nullable();
            $table->time('check_out_end_time')->nullable();
            $table->unsignedInteger('late_tolerance_minutes')->default(15);
            $table->boolean('is_overnight')->default(false);
            $table->boolean('is_active')->default(true);
            $this->auditColumns($table);
            $table->index(['is_active', 'delete_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('work_locations');
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
