<?php

namespace App\Services\Admin;

use App\Models\Setting;
use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class PackageDateRuleService
{
    private const GROUP_ID = 'package_date_rule';

    public function __construct(
        private SettingRepositoryInterface $settingRepository
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

    public function createRule(array $payload): Setting
    {
        $this->assertCodeUniqueness($payload['code']);

        /** @var Setting $rule */
        $rule = $this->settingRepository->create([
            'uuid' => (string) Str::uuid(),
            'code' => $payload['code'],
            'description' => $payload['description'],
            'group_id' => self::GROUP_ID,
            'type_id' => null,
            'value' => $payload['value'],
        ]);

        return $rule;
    }

    public function updateRule(Setting $setting, array $payload): Setting
    {
        $managedRule = $this->resolveEditableRule($setting);

        /** @var Setting $updatedRule */
        $updatedRule = $this->settingRepository->update($managedRule, [
            'description' => $payload['description'],
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
            throw new RuntimeException('Aturan ini bukan termasuk grup aturan waktu paket.');
        }

        return $setting;
    }

    public function resolveValueNote(string $value): string
    {
        $value = strtoupper(trim($value));

        if (preg_match('/^H\+(\d+)$/', $value, $matches)) {
            $days = (int) $matches[1];
            return "Dihitung {$days} hari setelah approval/paket dipilih.";
        }

        if (preg_match('/^H-(\d+)$/', $value, $matches)) {
            $days = (int) $matches[1];
            return "Dihitung {$days} hari sebelum tanggal acara booking.";
        }

        return '';
    }

    private function buildQuery(): Builder
    {
        return $this->settingRepository
            ->query(true)
            ->where('group_id', self::GROUP_ID)
            ->orderBy('code');
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
