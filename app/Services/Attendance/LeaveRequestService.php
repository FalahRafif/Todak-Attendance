<?php

namespace App\Services\Attendance;

use App\Repositories\Contracts\AttendanceCorrectionRequestRepositoryInterface;
use App\Repositories\Contracts\LeaveBalanceRepositoryInterface;
use App\Repositories\Contracts\LeaveRequestDetailRepositoryInterface;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeaveRequestService
{
    public function __construct(
        private LeaveRequestRepositoryInterface $leaveRequestRepository,
        private LeaveRequestDetailRepositoryInterface $leaveRequestDetailRepository,
        private LeaveBalanceRepositoryInterface $leaveBalanceRepository,
        private AttendanceCorrectionRequestRepositoryInterface $attendanceCorrectionRequestRepository
    ) {
    }

    public function createLeaveRequest(array $payload, array $dates = [])
    {
        return DB::transaction(function () use ($payload, $dates) {
            $leaveRequest = $this->leaveRequestRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));

            foreach ($dates as $date) {
                $this->leaveRequestDetailRepository->create([
                    'uuid' => (string) Str::uuid(),
                    'leave_request_id' => $leaveRequest->getKey(),
                    'leave_date' => $date,
                ]);
            }

            return $leaveRequest->refresh();
        });
    }

    public function createLeaveBalance(array $payload)
    {
        return $this->leaveBalanceRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }

    public function createCorrectionRequest(array $payload)
    {
        return $this->attendanceCorrectionRequestRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }
}
