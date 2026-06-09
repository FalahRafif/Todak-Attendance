<?php

use App\Models\User;

if (! function_exists('friendly_label')) {
    function friendly_label(?string $value): string
    {
        $key = strtolower(trim((string) $value));

        return match ($key) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'leave', 'annual_leave' => 'Cuti Tahunan',
            'sick', 'sick_leave' => 'Sakit',
            'permission' => 'Izin',
            'pending', 'pending_approval' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'check_in' => 'Masuk Kerja',
            'check_out' => 'Pulang Kerja',
            'update_by_hrd' => 'Diperbarui HRD',
            '' => '-',
            default => str($value)->replace('_', ' ')->title()->toString(),
        };
    }
}

if (! function_exists('panel_route')) {
    function panel_route(string $routeName, array $parameters = [], bool $absolute = true): string
    {
        if (str_starts_with($routeName, 'admin.')) {
            $currentPrefix = session('auth.role');

            if (! is_string($currentPrefix) || $currentPrefix === '') {
                $user = auth()->user();

                if ($user instanceof User) {
                    $roleName = $user->roleName();
                    $prefixByRole = config('role_access.route_prefix_by_role', []);
                    $currentPrefix = is_string($roleName) ? ($prefixByRole[$roleName] ?? 'admin') : 'admin';
                } else {
                    $currentPrefix = 'admin';
                }
            }

            $routeName = $currentPrefix.substr($routeName, strlen('admin'));
        }

        return route($routeName, $parameters, $absolute);
    }
}
