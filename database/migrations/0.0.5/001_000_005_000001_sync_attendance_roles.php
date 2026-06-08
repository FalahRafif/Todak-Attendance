<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $oldRoles = DB::table('roles')
            ->whereIn('name', ['admin', 'HRD', 'karyawan', 'Karyawan Kontrak', 'Interns'])
            ->pluck('id', 'name');

        if (isset($oldRoles['admin'])) {
            DB::table('roles')->where('id', $oldRoles['admin'])->update(['name' => 'Admin', 'updated_at' => $now]);
        }

        if (!isset($oldRoles['HRD']) && !DB::table('roles')->where('name', 'HRD')->exists()) {
            DB::table('roles')->insert(['uuid' => (string) Str::uuid(), 'name' => 'HRD', 'created_at' => $now, 'updated_at' => $now, 'delete_status' => false]);
        }

        if (isset($oldRoles['karyawan'])) {
            DB::table('roles')->where('id', $oldRoles['karyawan'])->update(['name' => 'Employee', 'updated_at' => $now]);
        } elseif (!DB::table('roles')->where('name', 'Employee')->exists()) {
            DB::table('roles')->insert(['uuid' => (string) Str::uuid(), 'name' => 'Employee', 'created_at' => $now, 'updated_at' => $now, 'delete_status' => false]);
        }

        DB::table('roles')->whereIn('name', ['Karyawan Kontrak', 'Interns'])->delete();

        $roleIds = DB::table('roles')->whereIn('name', ['Admin', 'HRD', 'Employee'])->pluck('id', 'name');
        DB::table('users')->where('email', 'admin@klikabsen.local')->update(['role_id' => $roleIds['Admin'] ?? null]);
        DB::table('users')->where('email', 'hrd@klikabsen.local')->update(['role_id' => $roleIds['HRD'] ?? null]);
        DB::table('users')->whereIn('email', ['karyawan@klikabsen.local', 'karyawan-kontrak@klikabsen.local', 'interns@klikabsen.local'])->update(['role_id' => $roleIds['Employee'] ?? null]);
        DB::table('users')->updateOrInsert(['email' => 'employee@klikabsen.local'], [
            'uuid' => (string) Str::uuid(),
            'name' => 'Employee',
            'username' => 'employee',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'remember_token' => null,
            'role_id' => $roleIds['Employee'] ?? null,
            'profile_image_attachment_id' => null,
            'created_at' => $now,
            'created_by' => null,
            'updated_at' => $now,
            'updated_by' => null,
            'deleted_at' => null,
            'deleted_by' => null,
            'delete_status' => false,
        ]);
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'employee@klikabsen.local')->delete();
    }
};
