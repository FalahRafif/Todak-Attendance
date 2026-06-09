<?php

namespace App\Services\Attendance;

use App\Repositories\Contracts\HolidayRepositoryInterface;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Repositories\Contracts\WorkLocationRepositoryInterface;
use Illuminate\Support\Str;

class AttendanceMasterService
{
    public function __construct(
        private WorkLocationRepositoryInterface $workLocationRepository,
        private ShiftRepositoryInterface $shiftRepository,
        private HolidayRepositoryInterface $holidayRepository
    ) {
    }

    public function createWorkLocation(array $payload)
    {
        return $this->workLocationRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }

    public function createShift(array $payload)
    {
        return $this->shiftRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }

    public function createHoliday(array $payload)
    {
        return $this->holidayRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }
}
