<?php

namespace App\Services\Admin;

use App\Models\Attachment;
use App\Models\Package;
use App\Repositories\Contracts\AttachmentRepositoryInterface;
use App\Repositories\Contracts\PackageBenefitRepositoryInterface;
use App\Repositories\Contracts\PackageRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Services\AttachmentSecurityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PackageService
{
    public function __construct(
        private PackageRepositoryInterface $packageRepository,
        private PackageBenefitRepositoryInterface $packageBenefitRepository,
        private ReferenceRepositoryInterface $referenceRepository,
        private AttachmentRepositoryInterface $attachmentRepository,
        private AttachmentSecurityService $attachmentSecurityService
    ) {
    }

    /**
     * @return array{
     *   packages: Collection<int, Package>,
     *   stats: array<string, int>
     * }
     */
    public function getPagePayload(?string $search = null): array
    {
        $packages = $this->buildPackagesQuery($search)->get();

        return [
            'packages' => $packages,
            'stats' => $this->buildStats($packages),
        ];
    }

    /**
     * @return array{
     *   statusOptions: Collection<int, \App\Models\Reference>,
     *   packageTypeOptions: Collection<int, \App\Models\Reference>
     * }
     */
    public function getFormPayload(): array
    {
        return [
            'statusOptions' => $this->statusOptions(),
            'packageTypeOptions' => $this->packageTypeOptions(),
        ];
    }

    public function createPackage(array $payload): Package
    {
        return DB::transaction(function () use ($payload): Package {
            $this->resolveAllowedStatusOrFail((int) $payload['status_id']);
            $this->resolveAllowedPackageTypeOrFail((int) $payload['package_type']);

            $packageData = [
                'uuid' => (string) Str::uuid(),
                'name' => $payload['name'],
                'description' => $payload['description'] ?? null,
                'price' => $payload['price'],
                'status_id' => (int) $payload['status_id'],
                'package_type' => (int) $payload['package_type'],
            ];

            if (!empty($payload['has_thumbnail']) && isset($payload['thumbnail']) && $payload['thumbnail'] instanceof UploadedFile) {
                $attachment = $this->uploadThumbnail($payload['thumbnail']);
                $packageData['thumbnail_attachment_id'] = (int) $attachment->getKey();
            }

            /** @var Package $package */
            $package = $this->packageRepository->create($packageData);

            $this->syncBenefits($package, $payload['benefits'] ?? []);

            return $package->loadMissing($this->packageRelations());
        });
    }

    public function updatePackage(Package $package, array $payload): Package
    {
        return DB::transaction(function () use ($package, $payload): Package {
            $managedPackage = $this->resolveEditablePackage($package);

            $this->resolveAllowedStatusOrFail((int) $payload['status_id']);
            $this->resolveAllowedPackageTypeOrFail((int) $payload['package_type']);

            $updateData = [
                'name' => $payload['name'],
                'description' => $payload['description'] ?? null,
                'price' => $payload['price'],
                'status_id' => (int) $payload['status_id'],
                'package_type' => (int) $payload['package_type'],
            ];

            if (!empty($payload['remove_thumbnail'])) {
                $this->removeExistingThumbnail($managedPackage);
                $updateData['thumbnail_attachment_id'] = null;
            } elseif (!empty($payload['has_thumbnail']) && isset($payload['thumbnail']) && $payload['thumbnail'] instanceof UploadedFile) {
                $this->removeExistingThumbnail($managedPackage);
                $attachment = $this->uploadThumbnail($payload['thumbnail']);
                $updateData['thumbnail_attachment_id'] = (int) $attachment->getKey();
            }

            /** @var Package $updatedPackage */
            $updatedPackage = $this->packageRepository->update($managedPackage, $updateData);

            $this->syncBenefits($updatedPackage, $payload['benefits'] ?? []);

            return $updatedPackage->loadMissing($this->packageRelations());
        });
    }

    public function deletePackage(Package $package): bool
    {
        $managedPackage = $this->resolveEditablePackage($package);

        return $this->packageRepository->delete($managedPackage, $this->resolveCurrentUserId());
    }

    public function resolveEditablePackage(Package $package): Package
    {
        $package->loadMissing($this->packageRelations());

        return $package;
    }

    /**
     * @param  Collection<int, Package>  $packages
     * @return array<string, int>
     */
    private function buildStats(Collection $packages): array
    {
        $activeCount = 0;
        $inactiveCount = 0;
        $draftCount = 0;

        foreach ($packages as $package) {
            $statusCode = strtoupper((string) ($package->status?->code ?? ''));

            if ($statusCode === 'PS_ACTIVE') {
                $activeCount++;
                continue;
            }

            if ($statusCode === 'PS_INACTIVE') {
                $inactiveCount++;
                continue;
            }

            if ($statusCode === 'PS_DRAFT') {
                $draftCount++;
            }
        }

        return [
            'total' => $packages->count(),
            'active' => $activeCount,
            'inactive' => $inactiveCount,
            'draft' => $draftCount,
        ];
    }

    private function buildPackagesQuery(?string $search = null): Builder
    {
        $query = $this->packageRepository
            ->query(true)
            ->with($this->packageRelations())
            ->orderByDesc('id');

        $keyword = trim((string) $search);
        if ($keyword === '') {
            return $query;
        }

        $query->where(function (Builder $builder) use ($keyword): void {
            $builder
                ->where('name', 'like', '%' . $keyword . '%')
                ->orWhere('description', 'like', '%' . $keyword . '%')
                ->orWhereHas('status', function (Builder $statusQuery) use ($keyword): void {
                    $statusQuery
                        ->where('code', 'like', '%' . $keyword . '%')
                        ->orWhere('description', 'like', '%' . $keyword . '%');
                })
                ->orWhereHas('packageType', function (Builder $typeQuery) use ($keyword): void {
                    $typeQuery
                        ->where('code', 'like', '%' . $keyword . '%')
                        ->orWhere('description', 'like', '%' . $keyword . '%');
                });
        });

        return $query;
    }

    private function syncBenefits(Package $package, array $benefits): void
    {
        $existingBenefits = $this->packageBenefitRepository
            ->query(true)
            ->where('package_id', (int) $package->getKey())
            ->get();

        foreach ($existingBenefits as $existing) {
            $this->packageBenefitRepository->delete($existing, $this->resolveCurrentUserId());
        }

        foreach ($benefits as $benefitText) {
            $this->packageBenefitRepository->create([
                'uuid' => (string) Str::uuid(),
                'package_id' => (int) $package->getKey(),
                'name' => $benefitText,
            ]);
        }
    }

    private function uploadThumbnail(UploadedFile $file): Attachment
    {
        $storagePayload = $this->attachmentSecurityService->storeEncryptedUploadedFile($file, 'package-thumbnails');
        $encryptedPath = trim((string) ($storagePayload['encrypted_path'] ?? ''));
        if ($encryptedPath === '') {
            throw new RuntimeException('Upload thumbnail gagal disimpan.');
        }

        $imageTypeReferenceId = $this->resolveImageTypeFileId();

        /** @var Attachment $attachment */
        $attachment = $this->attachmentRepository->create([
            'uuid' => (string) Str::uuid(),
            'name' => $file->getClientOriginalName() ?: 'thumbnail',
            'path' => $encryptedPath,
            'type_file' => $imageTypeReferenceId,
        ]);

        return $attachment;
    }

    private function removeExistingThumbnail(Package $package): void
    {
        $package->loadMissing('thumbnailAttachment');
        $existingAttachment = $package->thumbnailAttachment;

        if ($existingAttachment instanceof Attachment) {
            try {
                $this->attachmentRepository->delete($existingAttachment, $this->resolveCurrentUserId());
            } catch (\Throwable $throwable) {
            }
        }
    }

    private function resolveImageTypeFileId(): int
    {
        $reference = $this->referenceRepository
            ->query(true)
            ->where('group_id', 'type_file')
            ->where('code', 'TF_IMG')
            ->first(['id']);

        if (!$reference instanceof \App\Models\Reference) {
            throw new RuntimeException('Reference type file untuk gambar tidak ditemukan.');
        }

        return (int) $reference->getKey();
    }

    /**
     * @return Collection<int, \App\Models\Reference>
     */
    private function statusOptions(): Collection
    {
        return $this->referenceRepository
            ->query(true)
            ->where('group_id', 'package_status')
            ->orderByRaw("CASE WHEN code = ? THEN 0 WHEN code = ? THEN 1 WHEN code = ? THEN 2 ELSE 99 END", [
                'PS_ACTIVE',
                'PS_DRAFT',
                'PS_INACTIVE',
            ])
            ->get(['id', 'code', 'description'])
            ->unique('code')
            ->values();
    }

    /**
     * @return Collection<int, \App\Models\Reference>
     */
    private function packageTypeOptions(): Collection
    {
        return $this->referenceRepository
            ->query(true)
            ->where('group_id', 'package_type')
            ->orderByRaw("CASE WHEN code = ? THEN 0 WHEN code = ? THEN 1 ELSE 99 END", [
                'PKT_WEDDING',
                'PKT_NON_WEDDING',
            ])
            ->get(['id', 'code', 'description'])
            ->unique('code')
            ->values();
    }

    private function resolveAllowedStatusOrFail(int $statusId): void
    {
        $exists = $this->referenceRepository
            ->query(true)
            ->where('group_id', 'package_status')
            ->where('id', $statusId)
            ->exists();

        if (!$exists) {
            throw new RuntimeException('Status paket yang dipilih tidak valid.');
        }
    }

    private function resolveAllowedPackageTypeOrFail(int $packageTypeId): void
    {
        $exists = $this->referenceRepository
            ->query(true)
            ->where('group_id', 'package_type')
            ->where('id', $packageTypeId)
            ->exists();

        if (!$exists) {
            throw new RuntimeException('Tipe paket yang dipilih tidak valid.');
        }
    }

    /**
     * @return array<int, string>
     */
    private function packageRelations(): array
    {
        return [
            'status:id,code,description',
            'packageType:id,code,description',
            'thumbnailAttachment:id,uuid,name,path',
            'benefits' => function ($query): void {
                $query->where('delete_status', false)->orderBy('id');
            },
        ];
    }

    private function resolveCurrentUserId(): ?int
    {
        $authId = auth()->id();

        return is_int($authId) ? $authId : null;
    }
}
