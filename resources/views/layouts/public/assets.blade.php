<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dokumentasi pernikahan - elegan, minimalis, mewah">
    <title>{{ $title ?? 'Etherno' }}</title>

        @php
            $fontPath = public_path('assets/fonts/InstrumentSans-Regular.woff2');
        @endphp
        @if(file_exists($fontPath))
                <link rel="preload" href="{{ asset('assets/fonts/InstrumentSans-Regular.woff2') }}" as="font" type="font/woff2" crossorigin>
        @endif

        @if(file_exists(public_path('assets/libs/bootstrap/css/bootstrap.min.css')))
            <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}">
        @endif
        @if(file_exists(public_path('assets/css/icons.css')))
            <link rel="stylesheet" href="{{ asset('assets/css/icons.css') }}">
        @endif
        @php
            $publicCustomCssPath = public_path('assets/css/public-custom.css');
            $publicCustomCssVersion = file_exists($publicCustomCssPath) ? filemtime($publicCustomCssPath) : time();
        @endphp
        <link rel="stylesheet" href="{{ asset('assets/css/public-custom.css') }}?v={{ $publicCustomCssVersion }}">
        @if(request()->routeIs('booking.page'))
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
        @endif

        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">

</head>
<body>
