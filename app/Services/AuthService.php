<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public const SESSION_ROLE_KEY = 'auth.role';
    public const SESSION_USER_KEY = 'auth.user';

    public function __construct(private AttachmentSecurityService $attachmentSecurityService)
    {
    }

    public function attempt(string $email, string $password, bool $remember = false): bool
    {
        return Auth::attempt(['email' => $email, 'password' => $password], $remember);
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    public function isInternalUser(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        $internalRoles = config('role_access.roles.internal', [RoleName::Admin->value, RoleName::Hrd->value]);

        return $user->hasRole($internalRoles);
    }

    public function resolveDashboardRoute(?User $user): ?string
    {
        if ($user === null) {
            return null;
        }

        $dashboardByRole = config('role_access.dashboard_route_by_role', []);
        $roleName = $user->roleName();

        if (!is_string($roleName)) {
            return null;
        }

        return $dashboardByRole[$roleName] ?? null;
    }

    public function resolveRoutePrefix(?User $user): ?string
    {
        if ($user === null) {
            return null;
        }

        $prefixByRole = config('role_access.route_prefix_by_role', []);
        $roleName = $user->roleName();

        if (!is_string($roleName)) {
            return null;
        }

        $routePrefix = $prefixByRole[$roleName] ?? null;

        return is_string($routePrefix) && $routePrefix !== '' ? $routePrefix : null;
    }

    public function syncInternalSession(Request $request, User $user): void
    {
        $user->loadMissing(['role', 'profileImageAttachment']);
        $routePrefix = $this->resolveRoutePrefix($user);

        if ($routePrefix === null) {
            $this->clearInternalSession($request);

            return;
        }

        $dashboardRoute = $this->resolveDashboardRoute($user);
        $panelTitleByPrefix = config('role_access.panel_title_by_prefix', []);
        $roleName = $user->roleName();
        $currentUserSession = $request->session()->get(self::SESSION_USER_KEY, []);
        if (!is_array($currentUserSession)) {
            $currentUserSession = [];
        }

        $request->session()->put(self::SESSION_ROLE_KEY, $routePrefix);
        $request->session()->put(self::SESSION_USER_KEY, [
            'id' => $user->getKey(),
            'uuid' => $user->uuid,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $roleName,
            'route_prefix' => $routePrefix,
            'dashboard_route' => $dashboardRoute,
            'profile_image_url' => $this->attachmentSecurityService->generateTemporaryPreviewUrl($user->profileImageAttachment),
            'panel_title' => $panelTitleByPrefix[$routePrefix] ?? null,
            'logged_in_at' => $currentUserSession['logged_in_at'] ?? now()->toDateTimeString(),
        ]);
    }

    public function syncRoleSession(Request $request, User $user): void
    {
        $this->syncInternalSession($request, $user);
    }

    public function clearInternalSession(Request $request): void
    {
        $request->session()->forget([self::SESSION_ROLE_KEY, self::SESSION_USER_KEY]);
    }

    public function clearRoleSession(Request $request): void
    {
        $this->clearInternalSession($request);
    }
}
