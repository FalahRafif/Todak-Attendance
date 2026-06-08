<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('locations', function (Blueprint $table): void {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->string('wilayah_id', 13);
			$table->string('name');
			$table->unsignedBigInteger('level_id');
			$table->unsignedBigInteger('parent_id')->nullable();
			$table->timestamp('created_at')->nullable();
			$table->unsignedBigInteger('created_by')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->unsignedBigInteger('updated_by')->nullable();
			$table->timestamp('deleted_at')->nullable();
			$table->unsignedBigInteger('deleted_by')->nullable();
			$table->boolean('delete_status')->default(false);

			$table->index('name');
			$table->index('wilayah_id');
			$table->index('level_id');
			$table->index('parent_id');
            $table->index('delete_status');

			$table->foreign('wilayah_id')->references('kode')->on('wilayah');
			$table->foreign('level_id')->references('id')->on('references');
			$table->foreign('parent_id')->references('id')->on('locations')->nullOnDelete();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('locations');
	}
};
