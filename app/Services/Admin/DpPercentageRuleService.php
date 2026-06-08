<?php

namespace App\Services\Admin;

use App\Models\Setting;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class DpPercentageRuleService
{
    private const GROUP_ID = 'payment_type_price_percentage';

    public function __construct(
        private SettingRepositoryInterface $settingRepository,
        private ReferenceRepositoryInterface $referenceRepository
    ) {
    }

    /**
     * @return array{rules: Collection<int, Setting>}
     */
    public function getPagePayload(): array
    {
        return [
            'rules' => $this->buildQuery()->get(),
        ];
    }

    /**
     * @return array{packageTypeOptions: Collection<int, \App\Models\Reference>}
     */
    public function getFormPayload(): array
    {
        return [
            'packageTypeOptions' => $this->packageTypeOptions(),
        ];
    }

    public function createRule(array $payload): Setting
    {
        $this->assertCodeUniqueness($payload['code']);
        $this->resolveAllowedPackageTypeOrFail((int) $payload['type_id']);

        /** @var Setting $rule */
        $rule = $this->settingRepository->create([
            'uuid' => (string) Str::uuid(),
            'code' => $payload['code'],
            'description' => $payload['description'],
            'group_id' => self::GROUP_ID,
            'type_id' => (int) $payload['type_id'],
            'value' => $payload['value'],
        ]);

        return $rule;
    }

    public function updateRule(Setting $setting, array $payload): Setting
    {
        $managedRule = $this->resolveEditableRule($setting);
        $this->resolveAllowedPackageTypeOrFail((int) $payload['type_id']);

        /** @var Setting $updatedRule */
        $updatedRule = $this->settingRepository->update($managedRule, [
            'description' => $payload['description'],
            'type_id' => (int) $payload['type_id'],
            'value' => $payload['value'],
        ]);

        return $updatedRule;
    }

    public function deleteRule(Setting $setting): bool
    {
        $managedRule = $this->resolveEditableRule($setting);

        return $this->settingRepository->delete($managedRule, $this->resolveCurrentUserId());
    }

    public function resolveEditableRule(Setting $setting): Setting
    {
        if (strtolower((string) $setting->group_id) !== self::GROUP_ID) {
            throw new RuntimeException('Aturan ini bukan termasuk grup persentase DP.');
        }

        return $setting;
    }

    private function buildQuery(): Builder
    {
        return $this->settingRepository
            ->query(true)
            ->with(['type:id,code,description'])
            ->where('group_id', self::GROUP_ID)
            ->orderBy('code');
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

    private function resolveAllowedPackageTypeOrFail(int $typeId): void
    {
        $exists = $this->referenceRepository
            ->query(true)
            ->where('group_id', 'package_type')
            ->where('id', $typeId)
            ->exists();

        if (!$exists) {
            throw new RuntimeException('Tipe paket yang dipilih tidak valid.');
        }
    }

    private function assertCodeUniqueness(string $code): void
    {
        $exists = $this->settingRepository
            ->query(true)
            ->where('group_id', self::GROUP_ID)
            ->where('code', $code)
            ->exists();

        if ($exists) {
            throw new RuntimeException('Kode aturan sudah digunakan.');
        }
    }

    private function resolveCurrentUserId(): ?int
    {
        $authId = auth()->id();

        return is_int($authId) ? $authId : null;
    }
}
