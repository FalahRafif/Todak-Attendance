<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('users', function (Blueprint $table): void {
			if (!Schema::hasColumn('users', 'uuid')) {
				$table->uuid('uuid')->nullable()->unique();
			}

			if (!Schema::hasColumn('users', 'username')) {
				$table->string('username')->nullable()->index();
			}

			if (!Schema::hasColumn('users', 'role_id')) {
				$table->unsignedBigInteger('role_id')->nullable()->index();
			}

			if (!Schema::hasColumn('users', 'profile_image_attachment_id')) {
				$table->unsignedBigInteger('profile_image_attachment_id')->nullable()->index();
			}

			if (!Schema::hasColumn('users', 'created_by')) {
				$table->unsignedBigInteger('created_by')->nullable();
			}

			if (!Schema::hasColumn('users', 'updated_by')) {
				$table->unsignedBigInteger('updated_by')->nullable();
			}

			if (!Schema::hasColumn('users', 'deleted_at')) {
				$table->timestamp('deleted_at')->nullable();
			}

			if (!Schema::hasColumn('users', 'deleted_by')) {
				$table->unsignedBigInteger('deleted_by')->nullable();
			}

			if (!Schema::hasColumn('users', 'delete_status')) {
				$table->boolean('delete_status')->default(false)->index();
			}
		});

		if (
			Schema::hasColumn('users', 'role_id') &&
			!$this->foreignKeyExists('users', 'users_role_id_foreign')
		) {
			Schema::table('users', function (Blueprint $table): void {
				$table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
			});
		}

		if (
			Schema::hasColumn('users', 'profile_image_attachment_id') &&
			!$this->foreignKeyExists('users', 'users_profile_image_attachment_id_foreign')
		) {
			Schema::table('users', function (Blueprint $table): void {
				$table->foreign('profile_image_attachment_id')
					->references('id')
					->on('attachments')
					->nullOnDelete();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if ($this->foreignKeyExists('users', 'users_profile_image_attachment_id_foreign')) {
			Schema::table('users', function (Blueprint $table): void {
				$table->dropForeign(['profile_image_attachment_id']);
			});
		}

		if ($this->foreignKeyExists('users', 'users_role_id_foreign')) {
			Schema::table('users', function (Blueprint $table): void {
				$table->dropForeign(['role_id']);
			});
		}

		Schema::table('users', function (Blueprint $table): void {
			if (Schema::hasColumn('users', 'delete_status')) {
				$table->dropColumn('delete_status');
			}

			if (Schema::hasColumn('users', 'deleted_by')) {
				$table->dropColumn('deleted_by');
			}

			if (Schema::hasColumn('users', 'deleted_at')) {
				$table->dropColumn('deleted_at');
			}

			if (Schema::hasColumn('users', 'updated_by')) {
				$table->dropColumn('updated_by');
			}

			if (Schema::hasColumn('users', 'created_by')) {
				$table->dropColumn('created_by');
			}

			if (Schema::hasColumn('users', 'profile_image_attachment_id')) {
				$table->dropColumn('profile_image_attachment_id');
			}

			if (Schema::hasColumn('users', 'role_id')) {
				$table->dropColumn('role_id');
			}

			if (Schema::hasColumn('users', 'username')) {
				$table->dropColumn('username');
			}

			if (Schema::hasColumn('users', 'uuid')) {
				$table->dropColumn('uuid');
			}
		});
	}

	private function foreignKeyExists(string $tableName, string $constraintName): bool
	{
		$query = DB::table('information_schema.table_constraints')
			->where('table_name', $tableName)
			->where('constraint_name', $constraintName)
			->where('constraint_type', 'FOREIGN KEY');

		$driver = DB::connection()->getDriverName();

		if ($driver === 'pgsql') {
			$query->whereRaw('table_schema = current_schema()');
		} elseif ($driver === 'mysql') {
			$query->whereRaw('table_schema = database()');
		}

		return $query->exists();
	}
};
