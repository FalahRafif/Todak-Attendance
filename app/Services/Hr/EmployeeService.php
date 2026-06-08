<?php

namespace App\Services\Hr;

use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\EmployeeWorkLocationRepositoryInterface;
use Illuminate\Support\Str;

class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private EmployeeWorkLocationRepositoryInterface $employeeWorkLocationRepository
    ) {
    }

    public function createEmployee(array $payload)
    {
        return $this->employeeRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }

    public function assignWorkLocation(array $payload)
    {
        return $this->employeeWorkLocationRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }
}
