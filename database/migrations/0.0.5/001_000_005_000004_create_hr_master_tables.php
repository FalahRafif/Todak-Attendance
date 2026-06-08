<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('departments')->nullOnDelete();
            $this->auditColumns($table);
            $table->index(['parent_id', 'delete_status']);
        });

        Schema::create('positions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $this->auditColumns($table);
            $table->index(['department_id', 'delete_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
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
