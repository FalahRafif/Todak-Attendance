<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        foreach ([
            ['ATTENDANCE_RADIUS_METER', 'Default radius absensi dalam meter', '100'],
            ['REQUIRE_SELFIE', 'Wajib selfie saat absensi', 'true'],
            ['REQUIRE_GPS', 'Wajib GPS aktif saat absensi', 'true'],
            ['ALLOW_OUTSIDE_RADIUS', 'Izinkan absensi luar radius', 'true'],
            ['REQUIRE_OUTSIDE_RADIUS_NOTE', 'Wajib keterangan jika luar radius', 'true'],
            ['LATE_TOLERANCE_MINUTES', 'Toleransi keterlambatan dalam menit', '15'],
        ] as [$code, $description, $value]) {
            DB::table('settings')->updateOrInsert(
                ['group_id' => 'attendance', 'code' => $code],
                ['uuid' => (string) Str::uuid(), 'description' => $description, 'type_id' => null, 'value' => $value, 'created_at' => $now, 'updated_at' => $now, 'delete_status' => false]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('group_id', 'attendance')->whereIn('code', [
            'ATTENDANCE_RADIUS_METER',
            'REQUIRE_SELFIE',
            'REQUIRE_GPS',
            'ALLOW_OUTSIDE_RADIUS',
            'REQUIRE_OUTSIDE_RADIUS_NOTE',
            'LATE_TOLERANCE_MINUTES',
        ])->delete();
    }
};
