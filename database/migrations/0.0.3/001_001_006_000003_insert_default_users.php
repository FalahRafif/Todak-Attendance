<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$roleNames = ['admin', 'HRD', 'karyawan', 'Karyawan Kontrak', 'Interns'];
		$roleIds = DB::table('roles')
			->whereIn('name', $roleNames)
			->pluck('id', 'name');

		if ($roleIds->count() !== count($roleNames)) {
			throw new RuntimeException('Default roles (admin, HRD, karyawan, Karyawan Kontrak, Interns) must exist before inserting users.');
		}

		$now = now();

		$users = [
			[
				'name' => 'Admin',
				'username' => 'admin',
				'email' => 'admin@klikabsen.local',
				'role_name' => 'admin',
			],
			[
				'name' => 'HRD',
				'username' => 'hrd',
				'email' => 'hrd@klikabsen.local',
				'role_name' => 'HRD',
			],
			[
				'name' => 'Karyawan',
				'username' => 'karyawan',
				'email' => 'karyawan@klikabsen.local',
				'role_name' => 'karyawan',
			],
			[
				'name' => 'Karyawan Kontrak',
				'username' => 'karyawan-kontrak',
				'email' => 'karyawan-kontrak@klikabsen.local',
				'role_name' => 'Karyawan Kontrak',
			],
			[
				'name' => 'Interns',
				'username' => 'interns',
				'email' => 'interns@klikabsen.local',
				'role_name' => 'Interns',
			],
		];

		foreach ($users as $user) {
			DB::table('users')->updateOrInsert(
				['email' => $user['email']],
				[
					'uuid' => (string) Str::uuid(),
					'name' => $user['name'],
					'username' => $user['username'],
					'email_verified_at' => $now,
					'password' => Hash::make('password'),
					'remember_token' => null,
					'role_id' => (int) $roleIds[$user['role_name']],
					'profile_image_attachment_id' => null,
					'created_at' => $now,
					'created_by' => null,
					'updated_at' => $now,
					'updated_by' => null,
					'deleted_at' => null,
					'deleted_by' => null,
					'delete_status' => false,
				]
			);
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('users')
			->whereIn('email', [
				'admin@klikabsen.local',
				'hrd@klikabsen.local',
				'karyawan@klikabsen.local',
				'karyawan-kontrak@klikabsen.local',
				'interns@klikabsen.local',
			])
			->delete();
	}
};
