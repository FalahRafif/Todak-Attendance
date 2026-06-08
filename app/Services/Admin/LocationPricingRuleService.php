<?php

namespace App\Services\Admin;

use App\Models\Location;
use App\Models\LocationPricingRule;
use App\Models\Reference;
use App\Repositories\Contracts\LocationPricingRuleRepositoryInterface;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class LocationPricingRuleService
{
    /**
     * @var array{
     *     ids: array<int, int>,
     *     labels: array<int, string>,
     *     codes: array<int, string>,
     *     labels_by_code: array<string, string>,
     *     ids_by_code: array<string, int>
     * }|null
     */
    private ?array $locationLevelMeta = null;

    /**
     * @var Collection<int, Reference>|null
     */
    private ?Collection $priceTypeOptionsCache = null;

    public function __construct(
        private LocationPricingRuleRepositoryInterface $locationPricingRuleRepository,
        private LocationRepositoryInterface $locationRepository,
        private ReferenceRepositoryInterface $referenceRepository
    ) {
    }

    /**
     * @return array{
     *   rules: Collection<int, LocationPricingRule>,
     *   stats: array<string, int>
     * }
     */
    public function getPagePayload(?string $search = null): array
    {
        $rules = $this->buildRulesQuery($search)->get();

        return [
            'rules' => $rules,
            'stats' => $this->buildStats($rules),
        ];
    }

    /**
     * @return array{
     *   locationLevels: Collection<int, array{code: string, label: string}>,
     *   locationPath: array<string, int|string>|null,
     *   priceTypeOptions: Collection<int, Reference>
     * }
     */
    public function getFormPayload(?LocationPricingRule $managedRule = null): array
    {
        return [
            'locationLevels' => $this->locationLevelOptions(),
            'locationPath' => $managedRule instanceof LocationPricingRule
                ? $this->resolveLocationPath($managedRule)
                : null,
            'priceTypeOptions' => $this->priceTypeOptions(),
        ];
    }

    public function createRule(array $payload): LocationPricingRule
    {
        $locationId = (int) ($payload['location_id'] ?? 0);
        $priceTypeId = (int) ($payload['price_type'] ?? 0);

        $this->resolveAllowedLocationOrFail($locationId);
        $this->resolveAllowedPriceTypeOrFail($priceTypeId);
        $this->assertLocationRuleUniqueness($locationId, null);

        /** @var LocationPricingRule $rule */
        $rule = $this->locationPricingRuleRepository->create([
            'uuid' => (string) Str::uuid(),
            'location_id' => $locationId,
            'price_type' => $priceTypeId,
        ]);

        return $rule->loadMissing($this->rulesRelations());
    }

    public function updateRule(LocationPricingRule $rule, array $payload): LocationPricingRule
    {
        $managedRule = $this->resolveEditableRule($rule);
        $locationId = (int) ($payload['location_id'] ?? 0);
        $priceTypeId = (int) ($payload['price_type'] ?? 0);

        $this->resolveAllowedLocationOrFail($locationId);
        $this->resolveAllowedPriceTypeOrFail($priceTypeId);
        $this->assertLocationRuleUniqueness($locationId, (int) $managedRule->getKey());

        /** @var LocationPricingRule $updatedRule */
        $updatedRule = $this->locationPricingRuleRepository->update($managedRule, [
            'location_id' => $locationId,
            'price_type' => $priceTypeId,
        ]);

        return $updatedRule->loadMissing($this->rulesRelations());
    }

    /**
     * @return array<string, int|string>
     */
    private function resolveLocationPath(LocationPricingRule $rule): array
    {
        $rule->loadMissing($this->rulesRelations());
        $location = $rule->location;

        if (!$location instanceof Location) {
            return [];
        }

        $levelCode = strtoupper((string) ($location->level?->code ?? ''));
        $path = [
            'level_code' => $levelCode,
            'location_id' => (int) $location->getKey(),
            'province_id' => 0,
            'city_id' => 0,
            'district_id' => 0,
            'village_id' => 0,
        ];

        if ($levelCode === 'LL_PV') {
            $path['province_id'] = (int) $location->getKey();
            return $path;
        }

        if ($levelCode === 'LL_CT') {
            $path['city_id'] = (int) $location->getKey();
            $path['province_id'] = (int) ($location->parent?->getKey() ?? 0);
            return $path;
        }

        if ($levelCode === 'LL_KC') {
            $path['district_id'] = (int) $location->getKey();
            $path['city_id'] = (int) ($location->parent?->getKey() ?? 0);
            $path['province_id'] = (int) ($location->parent?->parent?->getKey() ?? 0);
            return $path;
        }

        if ($levelCode === 'LL_KL') {
            $path['village_id'] = (int) $location->getKey();
            $path['district_id'] = (int) ($location->parent?->getKey() ?? 0);
            $path['city_id'] = (int) ($location->parent?->parent?->getKey() ?? 0);
            $path['province_id'] = (int) ($location->parent?->parent?->parent?->getKey() ?? 0);
        }

        return $path;
    }

    public function deleteRule(LocationPricingRule $rule): bool
    {
        $managedRule = $this->resolveEditableRule($rule);

        return $this->locationPricingRuleRepository->delete($managedRule, $this->resolveCurrentUserId());
    }

    public function resolveEditableRule(LocationPricingRule $rule): LocationPricingRule
    {
        $rule->loadMissing($this->rulesRelations());

        $location = $rule->location;
        if (!$location instanceof Location) {
            throw new RuntimeException('Lokasi aturan harga tidak valid.');
        }

        if (!in_array((int) $location->level_id, $this->allowedLocationLevelIds(), true)) {
            throw new RuntimeException('Aturan harga ini berada di level lokasi yang tidak dikelola modul ini.');
        }

        return $rule;
    }

    /**
     * @param  Collection<int, LocationPricingRule>  $rules
     * @return array<string, int>
     */
    private function buildStats(Collection $rules): array
    {
        $provinceCount = 0;
        $cityCount = 0;
        $districtCount = 0;
        $villageCount = 0;

        foreach ($rules as $rule) {
            $levelCode = strtoupper((string) ($rule->location?->level?->code ?? ''));

            if ($levelCode === 'LL_PV') {
                $provinceCount++;
                continue;
            }

            if ($levelCode === 'LL_CT') {
                $cityCount++;
                continue;
            }

            if ($levelCode === 'LL_KC') {
                $districtCount++;
                continue;
            }

            if ($levelCode === 'LL_KL') {
                $villageCount++;
            }
        }

        return [
            'total' => $rules->count(),
            'province' => $provinceCount,
            'city' => $cityCount,
            'district' => $districtCount,
            'village' => $villageCount,
        ];
    }

    private function buildRulesQuery(?string $search = null): Builder
    {
        $query = $this->locationPricingRuleRepository
            ->query(true)
            ->with($this->rulesRelations())
            ->orderByDesc('id');

        $keyword = trim((string) $search);
        if ($keyword === '') {
            return $query;
        }

        $query->where(function (Builder $builder) use ($keyword): void {
            $builder
                ->whereHas('location', function (Builder $locationQuery) use ($keyword): void {
                    $locationQuery
                        ->where('name', 'like', '%' . $keyword . '%')
                        ->orWhereHas('parent', function (Builder $parentQuery) use ($keyword): void {
                            $parentQuery->where('name', 'like', '%' . $keyword . '%');
                        })
                        ->orWhereHas('parent.parent', function (Builder $parentQuery) use ($keyword): void {
                            $parentQuery->where('name', 'like', '%' . $keyword . '%');
                        })
                        ->orWhereHas('parent.parent.parent', function (Builder $parentQuery) use ($keyword): void {
                            $parentQuery->where('name', 'like', '%' . $keyword . '%');
                        });
                })
                ->orWhereHas('priceType', function (Builder $priceTypeQuery) use ($keyword): void {
                    $priceTypeQuery
                        ->where('code', 'like', '%' . $keyword . '%')
                        ->orWhere('description', 'like', '%' . $keyword . '%');
                });
        });

        return $query;
    }

    /**
     * @return Collection<int, Reference>
     */
    private function priceTypeOptions(): Collection
    {
        if ($this->priceTypeOptionsCache instanceof Collection) {
            return $this->priceTypeOptionsCache;
        }

        $this->priceTypeOptionsCache = $this->referenceRepository
            ->query(true)
            ->where('group_id', 'price_type')
            ->orderByRaw("CASE WHEN code = ? THEN 0 WHEN code = ? THEN 1 WHEN code = ? THEN 2 ELSE 99 END", [
                'PT_RG',
                'PT_SD',
                'PT_CS',
            ])
            ->orderBy('description')
            ->get(['id', 'code', 'description']);

        return $this->priceTypeOptionsCache;
    }

    /**
     * @return Collection<int, array{code: string, label: string}>
     */
    private function locationLevelOptions(): Collection
    {
        $labelsByCode = $this->locationLevelLabelsByCode();
        $orderedCodes = ['LL_PV', 'LL_CT', 'LL_KC', 'LL_KL'];

        return collect($orderedCodes)
            ->map(static fn (string $code): array => [
                'code' => $code,
                'label' => $labelsByCode[$code] ?? $code,
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{id: int, name: string}>
     */
    public function getLocationOptions(string $levelCode, ?int $parentId = null): Collection
    {
        $levelId = $this->locationLevelIdByCode($levelCode);
        if (!is_int($levelId)) {
            return collect();
        }

        $query = $this->locationRepository
            ->query(true)
            ->where('level_id', $levelId);

        if (strtoupper($levelCode) === 'LL_PV') {
            $query->whereNull('parent_id');
        } else {
            $parentId = is_int($parentId) && $parentId > 0 ? $parentId : null;
            if ($parentId === null) {
                return collect();
            }
            $query->where('parent_id', $parentId);
        }

        return $query
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(static fn (Location $location): array => [
                'id' => (int) $location->getKey(),
                'name' => $location->name,
            ])
            ->values();
    }

    /**
     * @return array<int, int>
     */
    private function allowedLocationLevelIds(): array
    {
        return $this->resolveLocationLevelMeta()['ids'];
    }

    /**
     * @return array{
     *     ids: array<int, int>,
     *     labels: array<int, string>,
     *     codes: array<int, string>,
     *     labels_by_code: array<string, string>,
     *     ids_by_code: array<string, int>
     * }
     */
    private function resolveLocationLevelMeta(): array
    {
        if (is_array($this->locationLevelMeta)) {
            return $this->locationLevelMeta;
        }

        $references = $this->referenceRepository
            ->query(true)
            ->where('group_id', 'location_level')
            ->whereIn('code', ['LL_PV', 'LL_CT', 'LL_KC', 'LL_KL'])
            ->get(['id', 'code', 'description']);

        $requiredCodes = [
            'LL_PV' => 'Provinsi',
            'LL_CT' => 'Kota/Kabupaten',
            'LL_KC' => 'Kecamatan',
            'LL_KL' => 'Kelurahan',
        ];
        $meta = [
            'ids' => [],
            'labels' => [],
            'codes' => [],
            'labels_by_code' => [],
            'ids_by_code' => [],
        ];

        foreach ($references as $reference) {
            $code = strtoupper((string) $reference->code);
            if (!array_key_exists($code, $requiredCodes)) {
                continue;
            }

            $id = (int) $reference->id;
            $meta['ids'][] = $id;
            $meta['labels'][$id] = $requiredCodes[$code];
            $meta['codes'][$id] = $code;
            $meta['labels_by_code'][$code] = $requiredCodes[$code];
            $meta['ids_by_code'][$code] = $id;
        }

        foreach (array_keys($requiredCodes) as $code) {
            if (!array_key_exists($code, $meta['ids_by_code'])) {
                throw new RuntimeException('Reference level lokasi tidak lengkap.');
            }
        }

        $this->locationLevelMeta = $meta;

        return $this->locationLevelMeta;
    }

    /**
     * @return array<string, string>
     */
    private function locationLevelLabelsByCode(): array
    {
        return $this->resolveLocationLevelMeta()['labels_by_code'];
    }

    private function locationLevelIdByCode(string $code): ?int
    {
        $code = strtoupper($code);
        $meta = $this->resolveLocationLevelMeta();

        return $meta['ids_by_code'][$code] ?? null;
    }

    private function resolveAllowedLocationOrFail(int $locationId): Location
    {
        $location = $this->locationRepository
            ->query(true)
            ->whereIn('level_id', $this->allowedLocationLevelIds())
            ->find($locationId, ['id', 'name', 'level_id', 'parent_id']);

        if (!$location instanceof Location) {
            throw new RuntimeException('Lokasi yang dipilih tidak valid untuk aturan harga.');
        }

        return $location;
    }

    private function resolveAllowedPriceTypeOrFail(int $priceTypeId): Reference
    {
        $priceType = $this->referenceRepository
            ->query(true)
            ->where('group_id', 'price_type')
            ->find($priceTypeId, ['id', 'code', 'description']);

        if (!$priceType instanceof Reference) {
            throw new RuntimeException('Tipe harga yang dipilih tidak valid.');
        }

        return $priceType;
    }

    private function assertLocationRuleUniqueness(int $locationId, ?int $ignoreRuleId): void
    {
        $query = $this->locationPricingRuleRepository
            ->query(true)
            ->where('location_id', $locationId);

        if (is_int($ignoreRuleId) && $ignoreRuleId > 0) {
            $query->whereKeyNot($ignoreRuleId);
        }

        if ($query->exists()) {
            throw new RuntimeException('Lokasi tersebut sudah memiliki aturan harga aktif.');
        }
    }

    /**
     * @return array<int, string>
     */
    private function rulesRelations(): array
    {
        return [
            'location:id,name,level_id,parent_id',
            'location.level:id,code,description',
            'location.parent:id,name,level_id,parent_id',
            'location.parent.parent:id,name,level_id,parent_id',
            'location.parent.parent.parent:id,name',
            'priceType:id,code,description',
        ];
    }

    private function resolveCurrentUserId(): ?int
    {
        $authId = auth()->id();

        return is_int($authId) ? $authId : null;
    }
}
