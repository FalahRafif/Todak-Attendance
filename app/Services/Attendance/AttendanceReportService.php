<?php

namespace App\Services\Attendance;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\ApprovalRepositoryInterface;
use App\Repositories\Contracts\AttendanceMonthlySummaryRepositoryInterface;
use Illuminate\Support\Str;

class AttendanceReportService
{
    public function __construct(
        private ApprovalRepositoryInterface $approvalRepository,
        private AttendanceMonthlySummaryRepositoryInterface $attendanceMonthlySummaryRepository,
        private ActivityLogRepositoryInterface $activityLogRepository
    ) {
    }

    public function createApproval(array $payload)
    {
        return $this->approvalRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }

    public function createMonthlySummary(array $payload)
    {
        return $this->attendanceMonthlySummaryRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }

    public function logActivity(array $payload)
    {
        return $this->activityLogRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }
}
