@php
    $statusCode = trim($__env->yieldContent('status_code'));
    if ($statusCode === '' && isset($exception) && method_exists($exception, 'getStatusCode')) {
        $statusCode = (string) $exception->getStatusCode();
    }

    $statusTitle = trim($__env->yieldContent('status_title'));
    if ($statusTitle === '') {
        $statusTitle = 'Terjadi Kesalahan';
    }

    $statusMessage = trim($__env->yieldContent('status_message'));
    if ($statusMessage === '') {
        $statusMessage = 'Halaman yang Anda buka mengalami kendala. Silakan coba kembali beberapa saat lagi.';
    }

    $statusHint = trim($__env->yieldContent('status_hint'));
    $title = trim(($statusCode !== '' ? "{$statusCode} - " : '') . $statusTitle . ' - Etherno');

    $homeUrl = \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/');
    $bookingUrl = \Illuminate\Support\Facades\Route::has('booking.page') ? route('booking.page') : url('/booking');
    $loginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login');

    $panelPrefixes = ['admin', 'petugas'];
    $segmentPrefix = strtolower((string) request()->segment(1));
    $routeName = request()->route()?->getName();
    $routePrefix = null;

    if (is_string($routeName) && str_contains($routeName, '.')) {
        $routePrefix = strtolower(strtok($routeName, '.'));
    }

    $sessionPrefix = strtolower((string) session('auth.role', ''));
    $userPrefix = null;
    $user = auth()->user();

    if ($user !== null && method_exists($user, 'roleName')) {
        $roleName = $user->roleName();
        if (is_string($roleName) && $roleName !== '') {
            $prefixByRole = config('role_access.route_prefix_by_role', []);
            $candidatePrefix = $prefixByRole[$roleName] ?? null;
            if (is_string($candidatePrefix) && $candidatePrefix !== '') {
                $userPrefix = strtolower($candidatePrefix);
            }
        }
    }

    $panelPrefix = 'admin';
    foreach ([$segmentPrefix, $routePrefix, $sessionPrefix, $userPrefix] as $prefixCandidate) {
        if (in_array($prefixCandidate, $panelPrefixes, true)) {
            $panelPrefix = $prefixCandidate;
            break;
        }
    }

    $isPanelContext = in_array($segmentPrefix, $panelPrefixes, true)
        || in_array($routePrefix, $panelPrefixes, true)
        || in_array($sessionPrefix, $panelPrefixes, true)
        || in_array($userPrefix, $panelPrefixes, true);

    $dashboardRoute = $panelPrefix . '.dashboard';
    $dashboardUrl = \Illuminate\Support\Facades\Route::has($dashboardRoute) ? route($dashboardRoute) : $homeUrl;
    $isAuthenticated = auth()->check();
@endphp

@include($isPanelContext ? 'errors.admin-layout' : 'errors.public-layout')
