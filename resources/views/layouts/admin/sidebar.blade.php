@php
    $user = auth()->user();
    $roleName = $user?->roleName();
    $sections = config('role_access.menu', []);
    $prefixByRole = config('role_access.route_prefix_by_role', []);
    $dashboardRouteByRole = config('role_access.dashboard_route_by_role', []);
    $routePrefix = is_string($roleName) ? ($prefixByRole[$roleName] ?? null) : null;
    $dashboardRoute = is_string($roleName) ? ($dashboardRouteByRole[$roleName] ?? 'admin.dashboard') : 'admin.dashboard';
@endphp

<aside class="app-sidebar sticky" id="sidebar">
    <div class="main-sidebar-header">
        <a href="{{ route($dashboardRoute) }}" class="header-logo">
            <img src="{{ asset('assets/etherno/public/icon_trans_2.png') }}" alt="logo" class="desktop-logo">
            <img src="{{ asset('assets/etherno/public/icon_trans_2.png') }}" alt="logo" class="toggle-logo">
            <img src="{{ asset('assets/etherno/public/icon_trans_white_1.png') }}" alt="logo" class="desktop-dark">
            <img src="{{ asset('assets/etherno/public/icon_trans_white_1.png') }}" alt="logo" class="toggle-dark">
            <img src="{{ asset('assets/etherno/public/icon_trans_2.png') }}" alt="logo" class="desktop-white">
            <img src="{{ asset('assets/etherno/public/icon_trans_2.png') }}" alt="logo" class="toggle-white">
        </a>
    </div>

    <div class="main-sidebar" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <ul class="main-menu">
                @foreach ($sections as $section)
                    @php
                        $visibleItems = collect($section['items'] ?? [])->filter(function (array $item) use ($roleName): bool {
                            $allowedRoles = $item['roles'] ?? [];

                            if ($roleName === null || empty($allowedRoles)) {
                                return false;
                            }

                            return in_array($roleName, $allowedRoles, true);
                        });
                    @endphp

                    @if ($visibleItems->isEmpty())
                        @continue
                    @endif

                    <li class="slide__category"><span class="category-name">{{ $section['section'] }}</span></li>

                    @foreach ($visibleItems as $item)
                        @if ($routePrefix === null)
                            @continue
                        @endif

                        @php
                            $routeName = $routePrefix . '.' . $item['route_name'];
                            $activePatterns = $item['active'] ?? [$item['route_name']];
                            if (!is_array($activePatterns)) {
                                $activePatterns = [$activePatterns];
                            }
                            $activePatterns = array_map(
                                static fn (string $pattern): string => $routePrefix . '.' . $pattern,
                                $activePatterns
                            );
                        @endphp

                        <li class="slide">
                            <a href="{{ route($routeName) }}" class="side-menu__item {{ request()->routeIs(...$activePatterns) ? 'active' : '' }}">
                                <span class="side-menu__label">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
