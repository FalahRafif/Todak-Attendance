<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table): void {
            $table->string('applicable_days', 50)->default('weekday')->after('is_overnight');
        });

        $now = now();
        DB::table('references')->updateOrInsert(
            ['group_id' => 'SHIFT_APPLICABLE_DAYS', 'code' => 'SAD_WEEKDAY'],
            ['uuid' => (string) Str::uuid(), 'description' => 'weekday', 'created_at' => $now, 'updated_at' => $now, 'delete_status' => false]
        );
        DB::table('references')->updateOrInsert(
            ['group_id' => 'SHIFT_APPLICABLE_DAYS', 'code' => 'SAD_WEEKEND'],
            ['uuid' => (string) Str::uuid(), 'description' => 'weekend', 'created_at' => $now, 'updated_at' => $now, 'delete_status' => false]
        );
        DB::table('references')->updateOrInsert(
            ['group_id' => 'SHIFT_APPLICABLE_DAYS', 'code' => 'SAD_ALL'],
            ['uuid' => (string) Str::uuid(), 'description' => 'all', 'created_at' => $now, 'updated_at' => $now, 'delete_status' => false]
        );
        DB::table('references')->updateOrInsert(
            ['group_id' => 'SHIFT_APPLICABLE_DAYS', 'code' => 'SAD_CUSTOM'],
            ['uuid' => (string) Str::uuid(), 'description' => 'custom', 'created_at' => $now, 'updated_at' => $now, 'delete_status' => false]
        );

        Schema::table('shifts', function (Blueprint $table): void {
            $table->json('custom_days')->nullable()->after('applicable_days');
        });

        DB::table('settings')->updateOrInsert(
            ['group_id' => 'attendance', 'code' => 'DEFAULT_ANNUAL_LEAVE_QUOTA'],
            ['uuid' => (string) Str::uuid(), 'value' => '12', 'description' => 'Default kuota cuti tahunan per tahun untuk employee baru', 'created_at' => $now, 'updated_at' => $now, 'delete_status' => false]
        );
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table): void {
            $table->dropColumn(['applicable_days', 'custom_days']);
        });

        DB::table('references')->where('group_id', 'SHIFT_APPLICABLE_DAYS')->delete();
        DB::table('settings')->where('group_id', 'attendance')->where('code', 'DEFAULT_ANNUAL_LEAVE_QUOTA')->delete();
    }
};
