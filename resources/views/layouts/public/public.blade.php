@include('layouts.public.assets')

@include('layouts.public.header')

<main class="public-main">
    @yield('content')
</main>

@include('layouts.public.footer')

@include('layouts.public.scripts')
