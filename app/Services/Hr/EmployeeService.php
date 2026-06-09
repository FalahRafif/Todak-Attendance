<?php

namespace App\Services\Hr;

use App\Services\AttachmentSecurityService;
use App\Repositories\Contracts\AttachmentRepositoryInterface;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\EmployeeWorkLocationRepositoryInterface;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Enums\RoleName;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WorkLocationRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private EmployeeWorkLocationRepositoryInterface $employeeWorkLocationRepository,
        private DepartmentRepositoryInterface $departmentRepository,
        private PositionRepositoryInterface $positionRepository,
        private WorkLocationRepositoryInterface $workLocationRepository,
        private ShiftRepositoryInterface $shiftRepository,
        private ReferenceRepositoryInterface $referenceRepository,
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private AttachmentRepositoryInterface $attachmentRepository,
        private AttachmentSecurityService $attachmentSecurityService
    ) {
    }

    public function pageData(): array
    {
        return [
            'title' => 'User & Employee Management',
            'items' => $this->userRepository->query()->with(['role', 'profileImageAttachment', 'employee.department', 'employee.position'])->latest('id')->paginate(15),
        ];
    }

    public function formData(?int $id = null): array
    {
        $user = $id === null ? null : $this->userRepository->findOrFail($id, ['*'], ['role', 'employee', 'profileImageAttachment']);

        return array_merge($this->masterData(), [
            'title' => $id === null ? 'Create User' : 'Edit User',
            'user' => $user,
            'employee' => $user?->employee,
            'profileImageUrl' => $this->attachmentSecurityService->generateTemporaryPreviewUrl($user?->profileImageAttachment),
        ]);
    }

    public function createEmployeeWithUser(array $payload)
    {
        return DB::transaction(function () use ($payload) {
            $user = $this->userRepository->create($this->userPayload($payload));
            $this->syncProfileImage($user->id, $payload['profile_image'] ?? null);

            if (!$this->isEmployeeRole((int) $payload['role_id'])) {
                return $user;
            }

            return $this->employeeRepository->create(array_merge($this->normalizeBooleans($this->employeePayload($payload), ['is_active']), ['uuid' => (string) Str::uuid(), 'user_id' => $user->id]));
        });
    }

    public function updateEmployee(int $id, array $payload)
    {
        return DB::transaction(function () use ($id, $payload) {
            $this->userRepository->update($id, $this->userPayload($payload, false));
            $this->syncProfileImage($id, $payload['profile_image'] ?? null);
            $employee = $this->employeeRepository->query()->where('user_id', $id)->first();

            if (!$this->isEmployeeRole((int) $payload['role_id'])) {
                if ($employee !== null) {
                    $this->employeeRepository->delete($employee);
                }

                return $this->userRepository->findOrFail($id);
            }

            $employeePayload = $this->normalizeBooleans($this->employeePayload($payload), ['is_active']);
            if ($employee === null) {
                return $this->employeeRepository->create(array_merge($employeePayload, ['uuid' => (string) Str::uuid(), 'user_id' => $id]));
            }

            return $this->employeeRepository->update($employee, $employeePayload);
        });
    }

    public function deleteEmployee(int $id, ?int $deletedBy = null): bool
    {
        $employee = $this->employeeRepository->query()->where('user_id', $id)->first();
        if ($employee !== null) {
            $this->employeeRepository->delete($employee, $deletedBy);
        }

        return $this->userRepository->delete($id, $deletedBy);
    }

    public function assignWorkLocation(array $payload)
    {
        return $this->employeeWorkLocationRepository->create(array_merge($payload, ['uuid' => (string) Str::uuid()]));
    }

    private function syncProfileImage(int $userId, mixed $file): void
    {
        if (!$file instanceof UploadedFile) {
            return;
        }

        $stored = $this->attachmentSecurityService->storeEncryptedProfileImage($file);
        $type = $this->referenceRepository->findBy('code', 'TF_IMG');
        $attachment = $this->attachmentRepository->create([
            'uuid' => (string) Str::uuid(),
            'name' => $file->getClientOriginalName(),
            'path' => $stored['encrypted_path'],
            'type_file' => $type?->id,
            'created_by' => auth()->id(),
        ]);

        $this->userRepository->update($userId, ['profile_image_attachment_id' => $attachment->id]);
    }

    private function masterData(): array
    {
        return [
            'roles' => $this->roleRepository->query()->whereIn('name', [RoleName::Admin->value, RoleName::Hrd->value, RoleName::Employee->value])->orderBy('name')->get(),
            'employeeRoleId' => $this->roleRepository->findBy('name', RoleName::Employee->value)?->id,
            'departments' => $this->departmentRepository->all(),
            'positions' => $this->positionRepository->all(),
            'workLocations' => $this->workLocationRepository->all(),
            'shifts' => $this->shiftRepository->all(),
            'employeeTypes' => $this->referenceRepository->query()->where('group_id', 'EMPLOYEE_TYPE')->orderBy('description')->get(),
        ];
    }

    private function userPayload(array $payload, bool $requirePassword = true): array
    {
        $data = ['uuid' => $payload['uuid'] ?? (string) Str::uuid(), 'name' => $payload['full_name'] ?: $payload['username'], 'username' => $payload['username'], 'email' => $payload['email'], 'role_id' => $payload['role_id']];
        if ($requirePassword || !empty($payload['password'])) {
            $data['password'] = Hash::make($payload['password']);
        }

        return $data;
    }

    private function isEmployeeRole(int $roleId): bool
    {
        return $this->roleRepository->find($roleId)?->name === RoleName::Employee->value;
    }

    private function employeePayload(array $payload): array
    {
        return collect($payload)->only(['employee_number', 'full_name', 'phone', 'gender', 'employee_type_id', 'department_id', 'position_id', 'work_location_id', 'shift_id', 'join_date', 'end_date', 'is_active'])->all();
    }

    private function normalizeBooleans(array $payload, array $keys): array
    {
        foreach ($keys as $key) {
            $payload[$key] = (bool) ($payload[$key] ?? false);
        }

        return $payload;
    }
}
