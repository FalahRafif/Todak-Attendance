<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->insertOrIgnore([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => null,
            'password' => Hash::make('password'),
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('email', 'test@example.com')
            ->delete();
    }
};
