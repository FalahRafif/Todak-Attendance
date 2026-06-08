@php
    $authUser = auth()->user();
    $sessionUser = session(\App\Services\AuthService::SESSION_USER_KEY, []);
    $sessionUser = is_array($sessionUser) ? $sessionUser : [];
    $displayName = trim((string) ($sessionUser['name'] ?? $authUser?->name ?? 'Internal User'));
    $displayRole = trim((string) ($sessionUser['role'] ?? (($authUser instanceof \App\Models\User) ? $authUser->roleName() : 'Internal')));
    $displayEmail = trim((string) ($sessionUser['email'] ?? $authUser?->email ?? ''));
@endphp

<header class="app-header">
    <div class="main-header-container container-fluid">
        <div class="header-content-left align-items-center">
            <div class="header-element">
                <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle" data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
            </div>
            <div class="header-element ms-2 d-none d-md-block">
                <div style="font-weight:900;color:#0f172a;letter-spacing:-.03em;">KlikAbsen Dashboard</div>
                <div style="font-size:12px;color:#64748b;">Sistem Absensi Karyawan</div>
            </div>
        </div>

        <div class="header-content-right">
            <div class="header-element dropdown">
                <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <span class="avatar avatar-sm" style="background:#0f4c81;color:#fff;font-weight:900;">{{ strtoupper(substr($displayName, 0, 1)) }}</span>
                    <span class="d-none d-xl-block ms-2">
                        <span class="fw-semibold d-block lh-1">{{ $displayName }}</span>
                        <span class="fs-12 text-muted">{{ $displayRole }}</span>
                    </span>
                </a>
                <ul class="main-header-dropdown dropdown-menu dropdown-menu-end pt-0 overflow-hidden">
                    <li class="px-3 py-3 border-bottom">
                        <div class="fw-semibold">{{ $displayName }}</div>
                        <div class="text-muted fs-12">{{ $displayEmail }}</div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center text-danger">
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
