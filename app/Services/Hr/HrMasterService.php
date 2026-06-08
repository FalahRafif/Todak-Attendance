<?php

namespace App\Services\Hr;

use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\HolidayRepositoryInterface;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Repositories\Contracts\WorkLocationRepositoryInterface;
use Illuminate\Support\Str;

class HrMasterService
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private PositionRepositoryInterface $positionRepository,
        private WorkLocationRepositoryInterface $workLocationRepository,
        private ShiftRepositoryInterface $shiftRepository,
        private HolidayRepositoryInterface $holidayRepository
    ) {
    }

    public function dashboardData(): array
    {
        return [
            'title' => 'Dashboard Admin',
            'stats' => [
                'Departments' => $this->departmentRepository->query()->count(),
                'Positions' => $this->positionRepository->query()->count(),
                'Work Locations' => $this->workLocationRepository->query()->count(),
                'Shifts' => $this->shiftRepository->query()->count(),
                'Holidays' => $this->holidayRepository->query()->count(),
            ],
        ];
    }

    public function departmentPageData(): array
    {
        return ['title' => 'Departments', 'items' => $this->departmentRepository->query()->with('parent')->latest('id')->paginate(15)];
    }

    public function departmentFormData(?int $id = null): array
    {
        return ['title' => $id === null ? 'Create Department' : 'Edit Department', 'item' => $id === null ? null : $this->departmentRepository->findOrFail($id), 'parents' => $this->departmentRepository->all()];
    }

    public function positionPageData(): array
    {
        return ['title' => 'Positions', 'items' => $this->positionRepository->query()->with('department')->latest('id')->paginate(15)];
    }

    public function positionFormData(?int $id = null): array
    {
        return ['title' => $id === null ? 'Create Position' : 'Edit Position', 'item' => $id === null ? null : $this->positionRepository->findOrFail($id), 'departments' => $this->departmentRepository->all()];
    }

    public function workLocationPageData(): array
    {
        return ['title' => 'Work Locations', 'items' => $this->workLocationRepository->query()->latest('id')->paginate(15)];
    }

    public function workLocationFormData(?int $id = null): array
    {
        return ['title' => $id === null ? 'Create Work Location' : 'Edit Work Location', 'item' => $id === null ? null : $this->workLocationRepository->findOrFail($id)];
    }

    public function shiftPageData(): array
    {
        return ['title' => 'Shifts', 'items' => $this->shiftRepository->query()->latest('id')->paginate(15)];
    }

    public function shiftFormData(?int $id = null): array
    {
        return ['title' => $id === null ? 'Create Shift' : 'Edit Shift', 'item' => $id === null ? null : $this->shiftRepository->findOrFail($id)];
    }

    public function holidayPageData(): array
    {
        return ['title' => 'Holidays', 'items' => $this->holidayRepository->query()->latest('holiday_date')->paginate(15)];
    }

    public function holidayFormData(?int $id = null): array
    {
        return ['title' => $id === null ? 'Create Holiday' : 'Edit Holiday', 'item' => $id === null ? null : $this->holidayRepository->findOrFail($id)];
    }

    public function createDepartment(array $payload)
    {
        return $this->departmentRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }

    public function updateDepartment(int $id, array $payload)
    {
        return $this->departmentRepository->update($id, $payload);
    }

    public function deleteDepartment(int $id, ?int $deletedBy = null): bool
    {
        return $this->departmentRepository->delete($id, $deletedBy);
    }

    public function createPosition(array $payload)
    {
        return $this->positionRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }

    public function updatePosition(int $id, array $payload)
    {
        return $this->positionRepository->update($id, $payload);
    }

    public function deletePosition(int $id, ?int $deletedBy = null): bool
    {
        return $this->positionRepository->delete($id, $deletedBy);
    }

    public function createWorkLocation(array $payload)
    {
        return $this->workLocationRepository->create(array_merge($this->normalizeBooleans($payload, ['is_default', 'is_active']), ['uuid' => (string) Str::uuid()]));
    }

    public function updateWorkLocation(int $id, array $payload)
    {
        return $this->workLocationRepository->update($id, $this->normalizeBooleans($payload, ['is_default', 'is_active']));
    }

    public function deleteWorkLocation(int $id, ?int $deletedBy = null): bool
    {
        return $this->workLocationRepository->delete($id, $deletedBy);
    }

    public function createShift(array $payload)
    {
        return $this->shiftRepository->create(array_merge($this->normalizeBooleans($payload, ['is_overnight', 'is_active']), ['uuid' => (string) Str::uuid()]));
    }

    public function updateShift(int $id, array $payload)
    {
        return $this->shiftRepository->update($id, $this->normalizeBooleans($payload, ['is_overnight', 'is_active']));
    }

    public function deleteShift(int $id, ?int $deletedBy = null): bool
    {
        return $this->shiftRepository->delete($id, $deletedBy);
    }

    public function createHoliday(array $payload)
    {
        return $this->holidayRepository->create(array_merge($this->normalizeBooleans($payload, ['is_national_holiday', 'is_company_holiday']), ['uuid' => (string) Str::uuid()]));
    }

    public function updateHoliday(int $id, array $payload)
    {
        return $this->holidayRepository->update($id, $this->normalizeBooleans($payload, ['is_national_holiday', 'is_company_holiday']));
    }

    public function deleteHoliday(int $id, ?int $deletedBy = null): bool
    {
        return $this->holidayRepository->delete($id, $deletedBy);
    }

    private function normalizeBooleans(array $payload, array $keys): array
    {
        foreach ($keys as $key) {
            $payload[$key] = (bool) ($payload[$key] ?? false);
        }

        return $payload;
    }
}
