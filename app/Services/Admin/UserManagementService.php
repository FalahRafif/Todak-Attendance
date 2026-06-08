<?php

namespace App\Services\Admin;

use App\Enums\RoleName;
use App\Models\User;
use App\Repositories\Contracts\AttachmentRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AttachmentSecurityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class UserManagementService
{
    private ?int $imageTypeReferenceId = null;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private ReferenceRepositoryInterface $referenceRepository,
        private AttachmentRepositoryInterface $attachmentRepository,
        private AttachmentSecurityService $attachmentSecurityService
    ) {
    }

    /**
     * @return array{
     *   roles: Collection<int, object>,
     *   users: Collection<int, User>,
     *   stats: array<string, int>
     * }
     */
    public function getPagePayload(?string $search = null): array
    {
        $roles = $this->availableRoles();
        $users = $this->buildManageableUsersQuery($roles, $search)->get();
        $this->appendProfileImageUrls($users);

        return [
            'roles' => $roles,
            'users' => $users,
            'stats' => $this->buildStats($users),
        ];
    }

    /**
     * @return array{users: array<int, array<string, mixed>>, stats: array<string, int>}
     */
    public function getUsersForApi(?string $search = null): array
    {
        $roles = $this->availableRoles();
        $users = $this->buildManageableUsersQuery($roles, $search)
            ->limit(250)
            ->get();

        return [
            'users' => $this->transformUsers($users),
            'stats' => $this->buildStats($users),
        ];
    }

    public function createUser(array $payload): User
    {
        $roleId = (int) ($payload['role_id'] ?? 0);
        $this->assertAllowedRoleId($roleId);

        $profileImage = $payload['profile_image'] ?? null;
        if ($profileImage !== null && !$profileImage instanceof UploadedFile) {
            throw new RuntimeException('Format upload foto profile tidak valid.');
        }

        /** @var User $createdUser */
        $createdUser = DB::transaction(function () use ($payload, $roleId, $profileImage): User {
            /** @var User $user */
            $user = $this->userRepository->create([
                'uuid' => (string) Str::uuid(),
                'name' => trim((string) ($payload['name'] ?? '')),
                'username' => $this->normalizeNullableString($payload['username'] ?? null),
                'email' => strtolower(trim((string) ($payload['email'] ?? ''))),
                'email_verified_at' => now(),
                'password' => (string) ($payload['password'] ?? ''),
                'role_id' => $roleId,
            ]);

            if ($profileImage instanceof UploadedFile) {
                $attachmentId = $this->storeProfileImageAttachment($profileImage);
                /** @var User $user */
                $user = $this->userRepository->update($user, [
                    'profile_image_attachment_id' => $attachmentId,
                ]);
            }

            return $user;
        });

        return $createdUser->loadMissing(['role', 'profileImageAttachment']);
    }

    public function updateUser(User $user, array $payload): User
    {
        $managedUser = $this->resolveManageableUser($user);

        $roleId = (int) ($payload['role_id'] ?? 0);
        $this->assertAllowedRoleId($roleId);

        $profileImage = $payload['profile_image'] ?? null;
        if ($profileImage !== null && !$profileImage instanceof UploadedFile) {
            throw new RuntimeException('Format upload foto profile tidak valid.');
        }

        $updatePayload = [
            'name' => trim((string) ($payload['name'] ?? '')),
            'username' => $this->normalizeNullableString($payload['username'] ?? null),
            'email' => strtolower(trim((string) ($payload['email'] ?? ''))),
            'role_id' => $roleId,
        ];

        $password = (string) ($payload['password'] ?? '');
        if ($password !== '') {
            $updatePayload['password'] = $password;
        }

        /** @var User $updatedUser */
        $updatedUser = DB::transaction(function () use ($managedUser, $updatePayload, $profileImage): User {
            /** @var User $entity */
            $entity = $this->userRepository->update($managedUser, $updatePayload);

            if ($profileImage instanceof UploadedFile) {
                $previousAttachmentId = (int) ($entity->profile_image_attachment_id ?? 0);
                $attachmentId = $this->storeProfileImageAttachment($profileImage);
                /** @var User $entity */
                $entity = $this->userRepository->update($entity, [
                    'profile_image_attachment_id' => $attachmentId,
                ]);

                if ($previousAttachmentId > 0 && $previousAttachmentId !== $attachmentId) {
                    try {
                        $this->attachmentRepository->delete($previousAttachmentId, $this->resolveCurrentUserId());
                    } catch (\Throwable $throwable) {
                        // Abaikan jika attachment lama sudah tidak tersedia.
                    }
                }
            }

            return $entity;
        });

        return $updatedUser->loadMissing(['role', 'profileImageAttachment']);
    }

    public function deleteUser(User $user): bool
    {
        $managedUser = $this->resolveManageableUser($user);

        $authId = auth()->id();
        $currentUserId = is_int($authId) ? $authId : null;

        if ($currentUserId !== null && $currentUserId === (int) $managedUser->getKey()) {
            throw new RuntimeException('Akun yang sedang login tidak dapat dihapus.');
        }

        return $this->userRepository->delete($managedUser, $currentUserId);
    }

    public function resolveManageableUser(User $user): User
    {
        $user->loadMissing(['role', 'profileImageAttachment']);

        if (!$user->hasRole($this->allowedRoleNames())) {
            throw new RuntimeException('Akun ini tidak termasuk scope manajemen user internal.');
        }

        return $user;
    }

    public function resolveProfileImageUrl(?User $user): ?string
    {
        if (!$user instanceof User) {
            return null;
        }

        $user->loadMissing('profileImageAttachment');

        return $this->attachmentSecurityService->generateTemporaryPreviewUrl($user->profileImageAttachment);
    }

    /**
     * @param  Collection<int, User>  $users
     * @return array<int, array<string, mixed>>
     */
    public function transformUsers(Collection $users): array
    {
        return $users
            ->values()
            ->map(function (User $user): array {
                $user->loadMissing(['role', 'profileImageAttachment']);

                return [
                    'id' => (int) $user->getKey(),
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'role_name' => $user->role?->name,
                    'created_at' => $user->created_at?->format('Y-m-d H:i') ?? '-',
                    'profile_image_url' => $this->resolveProfileImageUrl($user),
                ];
            })
            ->all();
    }

    /**
     * @param  Collection<int, User>  $users
     * @return array<string, int>
     */
    public function buildStats(Collection $users): array
    {
        $adminCount = 0;
        $petugasCount = 0;

        foreach ($users as $user) {
            $roleName = $user->roleName();

            if ($roleName === RoleName::Admin->value) {
                $adminCount++;
                continue;
            }

            if ($roleName === RoleName::Petugas->value) {
                $petugasCount++;
            }
        }

        return [
            'total' => $users->count(),
            'admin' => $adminCount,
            'petugas' => $petugasCount,
        ];
    }

    /**
     * @return Collection<int, object>
     */
    public function availableRoles(): Collection
    {
        $allowedRoleNames = $this->allowedRoleNames();

        return $this->roleRepository
            ->query(true)
            ->select(['id', 'name'])
            ->whereIn('name', $allowedRoleNames)
            ->orderByRaw("CASE WHEN name = ? THEN 0 WHEN name = ? THEN 1 ELSE 99 END", [
                RoleName::Admin->value,
                RoleName::Petugas->value,
            ])
            ->get();
    }

    /**
     * @param  Collection<int, object>  $roles
     */
    private function buildManageableUsersQuery(Collection $roles, ?string $search = null): Builder
    {
        $roleIds = $roles
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        $query = $this->userRepository
            ->query(true)
            ->with(['role', 'profileImageAttachment'])
            ->orderByDesc('id');

        if (empty($roleIds)) {
            $query->whereRaw('1 = 0');

            return $query;
        }

        $query->whereIn('role_id', $roleIds);

        $keyword = trim((string) $search);
        if ($keyword !== '') {
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder
                    ->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('username', 'like', '%' . $keyword . '%');
            });
        }

        return $query;
    }

    private function assertAllowedRoleId(int $roleId): void
    {
        $allowedRoleIds = $this->availableRoles()
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        if (!in_array($roleId, $allowedRoleIds, true)) {
            throw new RuntimeException('Role yang dipilih tidak valid untuk akun internal.');
        }
    }

    private function storeProfileImageAttachment(UploadedFile $file): int
    {
        $storagePayload = $this->attachmentSecurityService->storeEncryptedProfileImage($file);
        $encryptedPath = trim((string) ($storagePayload['encrypted_path'] ?? ''));
        if ($encryptedPath === '') {
            throw new RuntimeException('Upload foto profile gagal disimpan.');
        }

        $imageTypeReferenceId = $this->resolveImageTypeReferenceId();

        $attachment = $this->attachmentRepository->create([
            'uuid' => (string) Str::uuid(),
            'name' => $file->getClientOriginalName() ?: 'profile-image',
            'path' => $encryptedPath,
            'type_file' => $imageTypeReferenceId,
        ]);

        return (int) $attachment->getKey();
    }

    private function resolveImageTypeReferenceId(): int
    {
        if ($this->imageTypeReferenceId !== null) {
            return $this->imageTypeReferenceId;
        }

        $reference = $this->referenceRepository
            ->query(true)
            ->where('group_id', 'type_file')
            ->where('code', 'TF_IMG')
            ->first(['id']);

        if ($reference === null) {
            throw new RuntimeException('Reference type file untuk gambar tidak ditemukan.');
        }

        $this->imageTypeReferenceId = (int) $reference->id;

        return $this->imageTypeReferenceId;
    }

    /**
     * @return array<int, string>
     */
    private function allowedRoleNames(): array
    {
        return [RoleName::Admin->value, RoleName::Petugas->value];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function appendProfileImageUrls(Collection $users): void
    {
        foreach ($users as $user) {
            $user->setAttribute('profile_image_url', $this->resolveProfileImageUrl($user));
        }
    }

    private function resolveCurrentUserId(): ?int
    {
        $authId = auth()->id();

        return is_int($authId) ? $authId : null;
    }
}
