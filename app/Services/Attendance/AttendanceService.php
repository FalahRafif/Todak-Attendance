<?php

namespace App\Services\Attendance;

use App\Repositories\Contracts\AttendanceLogRepositoryInterface;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\EmployeeScheduleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceService
{
    public function __construct(
        private AttendanceRepositoryInterface $attendanceRepository,
        private AttendanceLogRepositoryInterface $attendanceLogRepository,
        private EmployeeScheduleRepositoryInterface $employeeScheduleRepository
    ) {
    }

    public function createSchedule(array $payload)
    {
        return $this->employeeScheduleRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }

    public function createAttendanceWithLog(array $attendancePayload, array $logPayload)
    {
        return DB::transaction(function () use ($attendancePayload, $logPayload) {
            $attendance = $this->attendanceRepository->create(array_merge($attendancePayload, ['uuid' => (string) Str::uuid()]));
            $this->attendanceLogRepository->create(array_merge($logPayload, [
                'uuid' => (string) Str::uuid(),
                'attendance_id' => $attendance->getKey(),
            ]));

            return $attendance->refresh();
        });
    }
}
