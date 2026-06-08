<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$now = now();

		$roles = ['admin', 'HRD', 'karyawan', 'Karyawan Kontrak', 'Interns'];

		DB::table('roles')->insert(array_map(static fn (string $role): array => [
			'uuid' => (string) Str::uuid(),
			'name' => $role,
			'created_at' => $now,
			'updated_at' => $now,
			'updated_by' => null,
			'deleted_at' => null,
			'deleted_by' => null,
			'delete_status' => false,
		], $roles));
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('roles')
			->whereIn('name', ['admin', 'HRD', 'karyawan', 'Karyawan Kontrak', 'Interns'])
			->delete();
	}
};
