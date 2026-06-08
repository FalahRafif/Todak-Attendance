<?php

namespace App\Services\Hr;

use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\PositionRepositoryInterface;
use Illuminate\Support\Str;

class HrMasterService
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private PositionRepositoryInterface $positionRepository
    ) {
    }

    public function createDepartment(array $payload)
    {
        return $this->departmentRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }

    public function createPosition(array $payload)
    {
        return $this->positionRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }
}
