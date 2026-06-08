<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Contracts\AttachmentRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AttachmentSecurityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ProfileService
{
    private ?int $imageTypeReferenceId = null;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ReferenceRepositoryInterface $referenceRepository,
        private AttachmentRepositoryInterface $attachmentRepository,
        private AttachmentSecurityService $attachmentSecurityService
    ) {
    }

    public function resolveProfileImageUrl(?User $user): ?string
    {
        if (!$user instanceof User) {
            return null;
        }

        $user->loadMissing('profileImageAttachment');

        return $this->attachmentSecurityService->generateTemporaryPreviewUrl($user->profileImageAttachment);
    }

    public function updateProfile(User $user, array $payload): User
    {
        $profileImage = $payload['profile_image'] ?? null;
        if ($profileImage !== null && !$profileImage instanceof UploadedFile) {
            throw new RuntimeException('Format upload foto profile tidak valid.');
        }

        $updatePayload = [
            'name' => trim((string) ($payload['name'] ?? '')),
            'username' => $this->normalizeNullableString($payload['username'] ?? null),
            'email' => strtolower(trim((string) ($payload['email'] ?? ''))),
        ];

        $password = (string) ($payload['password'] ?? '');
        if ($password !== '') {
            $updatePayload['password'] = $password;
        }

        /** @var User $updatedUser */
        $updatedUser = DB::transaction(function () use ($user, $updatePayload, $profileImage): User {
            /** @var User $entity */
            $entity = $this->userRepository->update($user, $updatePayload);

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

    private function storeProfileImageAttachment(UploadedFile $file): int
    {
        $storagePayload = $this->attachmentSecurityService->storeEncryptedProfileImage($file);
        $encryptedPath = trim((string) ($storagePayload['encrypted_path'] ?? ''));
        if ($encryptedPath === '') {
            throw new RuntimeException('Upload foto profile gagal disimpan.');
        }

        $attachment = $this->attachmentRepository->create([
            'uuid' => (string) Str::uuid(),
            'name' => $file->getClientOriginalName() ?: 'profile-image',
            'path' => $encryptedPath,
            'type_file' => $this->resolveImageTypeReferenceId(),
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

    private function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function resolveCurrentUserId(): ?int
    {
        $authId = auth()->id();

        return is_int($authId) ? $authId : null;
    }
}
