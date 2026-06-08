<?php

namespace App\Services\Portal;

use App\Models\Package;
use App\Repositories\Contracts\PackageRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class GuestPackageService
{
    private const ACTIVE_STATUS_CODE = 'PS_ACTIVE';

    private const WEDDING_PACKAGE_CODE = 'PKT_WEDDING';

    private const NON_WEDDING_PACKAGE_CODE = 'PKT_NON_WEDDING';

    public function __construct(private PackageRepositoryInterface $packageRepository)
    {
    }

    /**
     * @return array{
     *   weddingPackages: Collection<int, Package>,
     *   nonWeddingPackages: Collection<int, Package>
     * }
     */
    public function getLandingPayload(): array
    {
        $packages = $this->buildActivePackagesQuery()->get();

        return [
            'weddingPackages' => $this->filterPackagesByType($packages, self::WEDDING_PACKAGE_CODE)->take(3)->values(),
            'nonWeddingPackages' => $this->filterPackagesByType($packages, self::NON_WEDDING_PACKAGE_CODE)->take(3)->values(),
        ];
    }

    /**
     * @return array{
     *   weddingPackages: Collection<int, Package>,
     *   nonWeddingPackages: Collection<int, Package>
     * }
     */
    public function getAllPackagesPayload(): array
    {
        $packages = $this->buildActivePackagesQuery()->get();

        return [
            'weddingPackages' => $this->filterPackagesByType($packages, self::WEDDING_PACKAGE_CODE)->values(),
            'nonWeddingPackages' => $this->filterPackagesByType($packages, self::NON_WEDDING_PACKAGE_CODE)->values(),
        ];
    }

    private function buildActivePackagesQuery(): Builder
    {
        return $this->packageRepository
            ->query(true)
            ->with($this->packageRelations())
            ->whereHas('status', function (Builder $statusQuery): void {
                $statusQuery
                    ->where('group_id', 'package_status')
                    ->where('code', self::ACTIVE_STATUS_CODE);
            })
            ->whereHas('packageType', function (Builder $typeQuery): void {
                $typeQuery
                    ->where('group_id', 'package_type')
                    ->whereIn('code', [self::WEDDING_PACKAGE_CODE, self::NON_WEDDING_PACKAGE_CODE]);
            })
            ->orderBy('price')
            ->orderBy('id');
    }

    /**
     * @param  Collection<int, Package>  $packages
     * @return Collection<int, Package>
     */
    private function filterPackagesByType(Collection $packages, string $typeCode): Collection
    {
        return $packages
            ->filter(function (Package $package) use ($typeCode): bool {
                $currentTypeCode = strtoupper((string) ($package->packageType?->code ?? ''));

                return $currentTypeCode === $typeCode;
            })
            ->values();
    }

    /**
     * @return array<int, string>
     */
    private function packageRelations(): array
    {
        return [
            'status:id,code,description',
            'packageType:id,code,description',
            'benefits' => function (HasMany $query): void {
                $query->orderBy('id');
            },
        ];
    }
}
