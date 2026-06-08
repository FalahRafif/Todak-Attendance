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
		Schema::create('settings', function (Blueprint $table): void {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->string('code', 100);
			$table->text('description')->nullable();
			$table->string('group_id', 100)->nullable();
			$table->unsignedBigInteger('type_id')->nullable();
			$table->text('value')->nullable();
			$table->timestamp('created_at')->nullable();
			$table->unsignedBigInteger('created_by')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->unsignedBigInteger('updated_by')->nullable();
			$table->timestamp('deleted_at')->nullable();
			$table->unsignedBigInteger('deleted_by')->nullable();
			$table->boolean('delete_status')->default(false);

			$table->index('code');
			$table->index('group_id');
			$table->index('type_id');
			$table->index('delete_status');

			$table->foreign('type_id')->references('id')->on('references');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('settings');
	}
};
