<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->insertGroup('EMPLOYEE_TYPE', [
            ['EMP_PERMANENT', 'permanent'],
            ['EMP_CONTRACT', 'contract'],
            ['EMP_INTERN', 'intern'],
        ]);
        $this->insertGroup('ATTENDANCE_STATUS', [
            ['ATS_PRESENT', 'present'],
            ['ATS_LATE', 'late'],
            ['ATS_ABSENT', 'absent'],
            ['ATS_LEAVE', 'leave'],
            ['ATS_SICK', 'sick'],
            ['ATS_PERMISSION', 'permission'],
            ['ATS_INCOMPLETE', 'incomplete'],
            ['ATS_PENDING_APPROVAL', 'pending_approval'],
        ]);
        $this->insertGroup('LEAVE_TYPE', [
            ['LVT_ANNUAL_LEAVE', 'annual_leave'],
            ['LVT_SICK_LEAVE', 'sick_leave'],
            ['LVT_PERMISSION', 'permission'],
        ]);
        $this->insertGroup('APPROVAL_STATUS', [
            ['APS_PENDING', 'pending'],
            ['APS_APPROVED', 'approved'],
            ['APS_REJECTED', 'rejected'],
            ['APS_CANCELLED', 'cancelled'],
        ]);
        $this->insertGroup('WORK_MODE', [
            ['WKM_OFFICE', 'office'],
            ['WKM_WFH', 'wfh'],
            ['WKM_BUSINESS_TRIP', 'business_trip'],
            ['WKM_OUTSIDE_MEETING', 'outside_meeting'],
            ['WKM_CLIENT_VISIT', 'client_visit'],
        ]);
        $this->insertGroup('ATTENDANCE_ACTION_TYPE', [
            ['AAT_CHECK_IN', 'check_in'],
            ['AAT_CHECK_OUT', 'check_out'],
            ['AAT_UPDATE_BY_HRD', 'update_by_hrd'],
            ['AAT_APPROVAL_BY_HRD', 'approval_by_hrd'],
        ]);
    }

    public function down(): void
    {
        DB::table('references')->whereIn('group_id', [
            'EMPLOYEE_TYPE',
            'ATTENDANCE_STATUS',
            'LEAVE_TYPE',
            'APPROVAL_STATUS',
            'WORK_MODE',
            'ATTENDANCE_ACTION_TYPE',
        ])->delete();
    }

    private function insertGroup(string $groupId, array $items): void
    {
        $now = now();
        foreach ($items as [$code, $description]) {
            DB::table('references')->updateOrInsert(
                ['group_id' => $groupId, 'code' => $code],
                ['uuid' => (string) Str::uuid(), 'description' => $description, 'created_at' => $now, 'updated_at' => $now, 'delete_status' => false]
            );
        }
    }
};
