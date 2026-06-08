<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login — Etherno')</title>

    @php $fontPath = public_path('assets/fonts/InstrumentSans-Regular.woff2'); @endphp
    @if(file_exists($fontPath))
        <link rel="preload" href="{{ asset('assets/fonts/InstrumentSans-Regular.woff2') }}" as="font" type="font/woff2" crossorigin>
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/public-custom.css') }}">

    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
