<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class ActivityLogService
{
    public function log(string $module, string $action, string $description, ?array $oldValue = null, ?array $newValue = null, ?int $userId = null, ?Request $request = null): void
    {
        try {
            $request ??= request();
            ActivityLog::query()->create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $userId ?? auth()->id(),
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'created_by' => $userId ?? auth()->id(),
            ]);
        } catch (Throwable) {
        }
    }
}
