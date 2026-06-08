<!DOCTYPE html>
<html lang="id" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="light" data-toggled="close">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'KlikAbsen')</title>
    @include('layouts.admin.assets')
    @stack('styles')
</head>
<body>
    <div class="page">
        @include('layouts.admin.header')
        @include('layouts.admin.sidebar')
        <div class="main-content app-content">
            <div class="main-container container-fluid">
                @yield('content')
            </div>
        </div>
        @include('layouts.admin.footer')
    </div>
    @include('layouts.admin.scripts')
    @stack('scripts')
</body>
</html>
