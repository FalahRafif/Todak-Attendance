<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->syncRoles();
        $this->insertReferences();
        $this->insertSettings();
        $this->createMasterTables();
        $this->createScheduleTables();
        $this->createAttendanceTables();
        $this->createLeaveTables();
        $this->createApprovalAndAuditTables();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('attendance_monthly_summaries');
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('attendance_correction_requests');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_request_details');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('employee_schedules');
        Schema::dropIfExists('employee_work_locations');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('work_locations');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');

        DB::table('settings')
            ->where('group_id', 'attendance')
            ->whereIn('code', [
                'ATTENDANCE_RADIUS_METER',
                'REQUIRE_SELFIE',
                'REQUIRE_GPS',
                'ALLOW_OUTSIDE_RADIUS',
                'REQUIRE_OUTSIDE_RADIUS_NOTE',
                'LATE_TOLERANCE_MINUTES',
            ])
            ->delete();

        DB::table('references')
            ->whereIn('group_id', [
                'EMPLOYEE_TYPE',
                'ATTENDANCE_STATUS',
                'LEAVE_TYPE',
                'APPROVAL_STATUS',
                'WORK_MODE',
                'ATTENDANCE_ACTION_TYPE',
            ])
            ->delete();
    }

    private function syncRoles(): void
    {
        $now = now();

        $oldRoles = DB::table('roles')
            ->whereIn('name', ['admin', 'HRD', 'karyawan', 'Karyawan Kontrak', 'Interns'])
            ->pluck('id', 'name');

        if (isset($oldRoles['admin'])) {
            DB::table('roles')->where('id', $oldRoles['admin'])->update(['name' => 'Admin', 'updated_at' => $now]);
        }

        if (!isset($oldRoles['HRD'])) {
            DB::table('roles')->updateOrInsert(
                ['name' => 'HRD'],
                ['uuid' => (string) Str::uuid(), 'created_at' => $now, 'updated_at' => $now, 'delete_status' => false]
            );
        }

        $employeeRoleId = $oldRoles['karyawan'] ?? null;
        if ($employeeRoleId !== null) {
            DB::table('roles')->where('id', $employeeRoleId)->update(['name' => 'Employee', 'updated_at' => $now]);
        } else {
            DB::table('roles')->updateOrInsert(
                ['name' => 'Employee'],
                ['uuid' => (string) Str::uuid(), 'created_at' => $now, 'updated_at' => $now, 'delete_status' => false]
            );
        }

        DB::table('roles')->whereIn('name', ['Karyawan Kontrak', 'Interns'])->delete();

        $roleIds = DB::table('roles')->whereIn('name', ['Admin', 'HRD', 'Employee'])->pluck('id', 'name');

        DB::table('users')->where('email', 'admin@klikabsen.local')->update(['role_id' => $roleIds['Admin'] ?? null]);
        DB::table('users')->where('email', 'hrd@klikabsen.local')->update(['role_id' => $roleIds['HRD'] ?? null]);
        DB::table('users')
            ->whereIn('email', ['karyawan@klikabsen.local', 'karyawan-kontrak@klikabsen.local', 'interns@klikabsen.local'])
            ->update(['role_id' => $roleIds['Employee'] ?? null]);

        DB::table('users')->updateOrInsert(
            ['email' => 'employee@klikabsen.local'],
            [
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
            ]
        );
    }

    private function insertReferences(): void
    {
        $this->insertReferenceGroup('EMPLOYEE_TYPE', [
            ['EMP_PERMANENT', 'permanent'],
            ['EMP_CONTRACT', 'contract'],
            ['EMP_INTERN', 'intern'],
        ]);

        $this->insertReferenceGroup('ATTENDANCE_STATUS', [
            ['ATS_PRESENT', 'present'],
            ['ATS_LATE', 'late'],
            ['ATS_ABSENT', 'absent'],
            ['ATS_LEAVE', 'leave'],
            ['ATS_SICK', 'sick'],
            ['ATS_PERMISSION', 'permission'],
            ['ATS_INCOMPLETE', 'incomplete'],
            ['ATS_PENDING_APPROVAL', 'pending_approval'],
        ]);

        $this->insertReferenceGroup('LEAVE_TYPE', [
            ['LVT_ANNUAL_LEAVE', 'annual_leave'],
            ['LVT_SICK_LEAVE', 'sick_leave'],
            ['LVT_PERMISSION', 'permission'],
        ]);

        $this->insertReferenceGroup('APPROVAL_STATUS', [
            ['APS_PENDING', 'pending'],
            ['APS_APPROVED', 'approved'],
            ['APS_REJECTED', 'rejected'],
            ['APS_CANCELLED', 'cancelled'],
        ]);

        $this->insertReferenceGroup('WORK_MODE', [
            ['WKM_OFFICE', 'office'],
            ['WKM_WFH', 'wfh'],
            ['WKM_BUSINESS_TRIP', 'business_trip'],
            ['WKM_OUTSIDE_MEETING', 'outside_meeting'],
            ['WKM_CLIENT_VISIT', 'client_visit'],
        ]);

        $this->insertReferenceGroup('ATTENDANCE_ACTION_TYPE', [
            ['AAT_CHECK_IN', 'check_in'],
            ['AAT_CHECK_OUT', 'check_out'],
            ['AAT_UPDATE_BY_HRD', 'update_by_hrd'],
            ['AAT_APPROVAL_BY_HRD', 'approval_by_hrd'],
        ]);
    }

    private function insertSettings(): void
    {
        $now = now();
        $settings = [
            ['ATTENDANCE_RADIUS_METER', 'Default radius absensi dalam meter', '100'],
            ['REQUIRE_SELFIE', 'Wajib selfie saat absensi', 'true'],
            ['REQUIRE_GPS', 'Wajib GPS aktif saat absensi', 'true'],
            ['ALLOW_OUTSIDE_RADIUS', 'Izinkan absensi luar radius', 'true'],
            ['REQUIRE_OUTSIDE_RADIUS_NOTE', 'Wajib keterangan jika luar radius', 'true'],
            ['LATE_TOLERANCE_MINUTES', 'Toleransi keterlambatan dalam menit', '15'],
        ];

        foreach ($settings as [$code, $description, $value]) {
            DB::table('settings')->updateOrInsert(
                ['group_id' => 'attendance', 'code' => $code],
                [
                    'uuid' => (string) Str::uuid(),
                    'description' => $description,
                    'type_id' => null,
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'delete_status' => false,
                ]
            );
        }
    }

    private function createMasterTables(): void
    {
        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('departments')->nullOnDelete();
            $this->auditColumns($table);
            $table->index(['parent_id', 'delete_status']);
        });

        Schema::create('positions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $this->auditColumns($table);
            $table->index(['department_id', 'delete_status']);
        });
    }

    private function createScheduleTables(): void
    {
        Schema::create('work_locations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('radius_meter')->default(100);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $this->auditColumns($table);
            $table->index(['is_default', 'delete_status']);
            $table->index(['is_active', 'delete_status']);
        });

        Schema::create('shifts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->time('check_in_start_time')->nullable();
            $table->time('check_in_end_time')->nullable();
            $table->time('check_out_start_time')->nullable();
            $table->time('check_out_end_time')->nullable();
            $table->unsignedInteger('late_tolerance_minutes')->default(15);
            $table->boolean('is_overnight')->default(false);
            $table->boolean('is_active')->default(true);
            $this->auditColumns($table);
            $table->index(['is_active', 'delete_status']);
        });

        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_number')->unique();
            $table->string('full_name');
            $table->string('phone', 30)->nullable();
            $table->string('gender', 20)->nullable();
            $table->foreignId('employee_type_id')->nullable()->constrained('references')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('work_location_id')->nullable()->constrained('work_locations')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('join_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $this->auditColumns($table);
            $table->index(['user_id', 'delete_status']);
            $table->index(['employee_type_id', 'delete_status']);
            $table->index(['department_id', 'delete_status']);
            $table->index(['position_id', 'delete_status']);
            $table->index(['work_location_id', 'delete_status']);
            $table->index(['shift_id', 'delete_status']);
            $table->index(['is_active', 'delete_status']);
        });

        Schema::create('employee_work_locations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('work_location_id')->constrained('work_locations')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $this->auditColumns($table);
            $table->unique(['employee_id', 'work_location_id'], 'ewl_employee_work_location_unique');
            $table->index(['work_location_id', 'delete_status']);
        });

        Schema::create('employee_schedules', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('schedule_date');
            $table->boolean('is_day_off')->default(false);
            $table->text('note')->nullable();
            $this->auditColumns($table);
            $table->unique(['employee_id', 'schedule_date'], 'employee_schedules_employee_date_unique');
            $table->index(['shift_id', 'delete_status']);
            $table->index(['schedule_date', 'delete_status']);
        });

        Schema::create('holidays', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->date('holiday_date');
            $table->text('description')->nullable();
            $table->boolean('is_national_holiday')->default(false);
            $table->boolean('is_company_holiday')->default(false);
            $this->auditColumns($table);
            $table->index(['holiday_date', 'delete_status']);
        });
    }

    private function createAttendanceTables(): void
    {
        Schema::create('attendances', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->restrictOnDelete();
            $table->foreignId('work_location_id')->constrained('work_locations')->restrictOnDelete();
            $table->date('attendance_date');
            $table->dateTime('check_in_at')->nullable();
            $table->foreignId('check_in_photo_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->decimal('check_in_distance_meter', 10, 2)->nullable();
            $table->boolean('check_in_is_inside_radius')->nullable();
            $table->foreignId('check_in_work_mode_id')->nullable()->constrained('references')->nullOnDelete();
            $table->text('check_in_note')->nullable();
            $table->text('check_in_device_info')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->foreignId('check_out_photo_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->decimal('check_out_distance_meter', 10, 2)->nullable();
            $table->boolean('check_out_is_inside_radius')->nullable();
            $table->foreignId('check_out_work_mode_id')->nullable()->constrained('references')->nullOnDelete();
            $table->text('check_out_note')->nullable();
            $table->text('check_out_device_info')->nullable();
            $table->unsignedInteger('total_work_minutes')->default(0);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('early_leave_minutes')->default(0);
            $table->foreignId('status_id')->constrained('references')->restrictOnDelete();
            $table->boolean('is_need_approval')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_note')->nullable();
            $this->auditColumns($table);
            $table->unique(['employee_id', 'attendance_date'], 'attendances_employee_date_unique');
            $table->index(['attendance_date', 'delete_status']);
            $table->index(['status_id', 'delete_status']);
            $table->index(['work_location_id', 'delete_status']);
            $table->index(['is_need_approval', 'delete_status']);
        });

        Schema::create('attendance_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('action_type_id')->constrained('references')->restrictOnDelete();
            $table->dateTime('action_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('distance_meter', 10, 2)->nullable();
            $table->boolean('is_inside_radius')->nullable();
            $table->foreignId('work_mode_id')->nullable()->constrained('references')->nullOnDelete();
            $table->text('note')->nullable();
            $table->foreignId('photo_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->text('device_info')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $this->auditColumns($table);
            $table->index(['attendance_id', 'delete_status']);
            $table->index(['employee_id', 'delete_status']);
            $table->index(['action_at', 'delete_status']);
        });
    }

    private function createLeaveTables(): void
    {
        Schema::create('leave_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('references')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total_days')->default(1);
            $table->text('reason')->nullable();
            $table->foreignId('attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->foreignId('status_id')->constrained('references')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_note')->nullable();
            $table->text('rejected_reason')->nullable();
            $this->auditColumns($table);
            $table->index(['employee_id', 'delete_status']);
            $table->index(['leave_type_id', 'delete_status']);
            $table->index(['status_id', 'delete_status']);
            $table->index(['start_date', 'delete_status']);
            $table->index(['end_date', 'delete_status']);
        });

        Schema::create('leave_request_details', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->cascadeOnDelete();
            $table->date('leave_date');
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $this->auditColumns($table);
            $table->index(['leave_request_id', 'delete_status']);
            $table->index(['leave_date', 'delete_status']);
            $table->index(['attendance_id', 'delete_status']);
        });

        Schema::create('leave_balances', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('total_quota')->default(0);
            $table->unsignedInteger('used_quota')->default(0);
            $table->unsignedInteger('remaining_quota')->default(0);
            $this->auditColumns($table);
            $table->unique(['employee_id', 'year'], 'leave_balances_employee_year_unique');
        });

        Schema::create('attendance_correction_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->date('correction_date');
            $table->dateTime('requested_check_in_at')->nullable();
            $table->dateTime('requested_check_out_at')->nullable();
            $table->text('reason');
            $table->foreignId('attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->foreignId('status_id')->constrained('references')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_note')->nullable();
            $table->text('rejected_reason')->nullable();
            $this->auditColumns($table);
            $table->index(['employee_id', 'delete_status'], 'acr_employee_delete_idx');
            $table->index(['attendance_id', 'delete_status'], 'acr_attendance_delete_idx');
            $table->index(['status_id', 'delete_status'], 'acr_status_delete_idx');
            $table->index(['correction_date', 'delete_status'], 'acr_date_delete_idx');
        });
    }

    private function createApprovalAndAuditTables(): void
    {
        Schema::create('approvals', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('status_id')->constrained('references')->restrictOnDelete();
            $table->unsignedInteger('approval_level')->default(1);
            $table->text('note')->nullable();
            $table->dateTime('approved_at')->nullable();
            $this->auditColumns($table);
            $table->index(['approvable_type', 'approvable_id']);
            $table->index(['status_id', 'delete_status']);
        });

        Schema::create('attendance_monthly_summaries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('total_work_days')->default(0);
            $table->unsignedInteger('total_present')->default(0);
            $table->unsignedInteger('total_late')->default(0);
            $table->unsignedInteger('total_absent')->default(0);
            $table->unsignedInteger('total_sick')->default(0);
            $table->unsignedInteger('total_leave')->default(0);
            $table->unsignedInteger('total_permission')->default(0);
            $table->unsignedInteger('total_incomplete')->default(0);
            $table->unsignedInteger('total_outside_radius')->default(0);
            $table->unsignedInteger('total_work_minutes')->default(0);
            $table->unsignedInteger('total_late_minutes')->default(0);
            $table->unsignedInteger('total_early_leave_minutes')->default(0);
            $table->dateTime('generated_at')->nullable();
            $this->auditColumns($table);
            $table->unique(['employee_id', 'year', 'month'], 'attendance_monthly_employee_period_unique');
            $table->index(['year', 'month']);
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('module', 100);
            $table->string('action', 100);
            $table->text('description')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $this->auditColumns($table);
            $table->index(['user_id', 'delete_status']);
            $table->index(['module', 'delete_status']);
            $table->index(['action', 'delete_status']);
            $table->index(['created_at', 'delete_status']);
        });
    }

    private function insertReferenceGroup(string $groupId, array $items): void
    {
        $now = now();

        foreach ($items as [$code, $description]) {
            DB::table('references')->updateOrInsert(
                ['group_id' => $groupId, 'code' => $code],
                [
                    'uuid' => (string) Str::uuid(),
                    'description' => $description,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'delete_status' => false,
                ]
            );
        }
    }

    private function auditColumns(Blueprint $table): void
    {
        $table->timestamp('created_at')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamp('updated_at')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamp('deleted_at')->nullable();
        $table->unsignedBigInteger('deleted_by')->nullable();
        $table->boolean('delete_status')->default(false);
        $table->index('delete_status');
    }
};
