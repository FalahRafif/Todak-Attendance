<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class UserProfileService
{
    public function __construct(
        private readonly AttachmentSecurityService $attachmentSecurityService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function profileData(): array
    {
        $user = $this->user()->load(['profileImageAttachment', 'employee.department', 'employee.position', 'employee.workLocation', 'employee.shift']);

        return [
            'title' => 'Profil Saya',
            'user' => $user,
            'profileImageUrl' => $this->attachmentSecurityService->generateTemporaryPreviewUrl($user->profileImageAttachment),
        ];
    }

    public function updateProfile(array $payload): void
    {
        $user = $this->user();

        DB::transaction(function () use ($user, $payload): void {
            $userPayload = [
                'name' => $payload['full_name'],
                'username' => $payload['username'],
                'email' => $payload['email'],
                'updated_by' => auth()->id(),
            ];

            if (! empty($payload['password'])) {
                $userPayload['password'] = Hash::make($payload['password']);
            }

            if (! empty($payload['profile_image'])) {
                $userPayload['profile_image_attachment_id'] = $this->storeProfilePhoto($payload['profile_image']);
            }

            User::query()->whereKey($user->id)->update($userPayload);

            if ($user->employee !== null) {
                $user->employee->update([
                    'full_name' => $payload['full_name'],
                    'phone' => $payload['phone'] ?? null,
                    'gender' => $payload['gender'] ?? null,
                    'updated_by' => auth()->id(),
                ]);
            }
        });

        $this->activityLogService->log('profile', 'update', 'User update profile', null, ['user_id' => $user->id]);
    }

    private function user(): User
    {
        $user = User::query()->with(['employee'])->whereKey(auth()->id())->first();

        if ($user === null) {
            abort(403, 'User tidak ditemukan.');
        }

        return $user;
    }

    private function storeProfilePhoto(UploadedFile $file): int
    {
        $stored = $this->attachmentSecurityService->storeEncryptedProfileImage($file);

        return Attachment::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => $file->getClientOriginalName(),
            'path' => $stored['encrypted_path'],
            'type_file' => $this->profileImageTypeId(),
            'created_by' => auth()->id(),
        ])->id;
    }

    private function profileImageTypeId(): int
    {
        $id = \App\Models\Reference::query()->where('code', 'TF_IMG')->value('id');

        if ($id === null) {
            throw new \RuntimeException('Reference TF_IMG belum tersedia.');
        }

        return $id;
    }
}
