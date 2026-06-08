<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="light" data-toggled="close">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $title ?? 'Nowa - Bootstrap 5 Premium Admin & Dashboard Template' }}</title>
    <meta name="Description" content="Bootstrap Responsive Admin Web Dashboard HTML5 Template">
    <meta name="Author" content="Spruko Technologies Private Limited">
	<meta name="keywords" content="admin,admin dashboard,admin panel,admin template,bootstrap,clean,dashboard,flat,jquery,modern,responsive,premium admin templates,responsive admin,ui,ui kit.">
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">
    
    <!-- Choices JS -->
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

    <!-- Main Theme Js -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    
    <!-- Bootstrap Css -->
    <link id="style" href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" >

    <!-- Style Css -->
    <link href="{{ asset('assets/css/styles.min.css') }}" rel="stylesheet" >

    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet" >

    <!-- Node Waves Css -->
    <link href="{{ asset('assets/libs/node-waves/waves.min.css') }}" rel="stylesheet" > 

    <!-- Simplebar Css -->
    <link href="{{ asset('assets/libs/simplebar/simplebar.min.css') }}" rel="stylesheet" >
    
    <!-- Color Picker Css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@simonwep/pickr/themes/nano.min.css') }}">

    <!-- Choices Css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">

    <style>
        :root {
            --primary-rgb: 15, 76, 129;
            --primary-color: #0f4c81;
            --default-border: #dbe4f0;
        }

        body {
            background-color: #f8fbff;
        }

        [data-theme-mode="dark"] body,
        [data-theme-mode="dark"] .page,
        [data-theme-mode="dark"] .main-content,
        [data-theme-mode="dark"] .app-content,
        [data-theme-mode="dark"] .main-container {
            background-color: var(--default-body-bg-color, #111827) !important;
            color: var(--default-text-color, #e5e7eb);
        }

        [data-theme-mode="dark"] .card,
        [data-theme-mode="dark"] .custom-card,
        [data-theme-mode="dark"] .table,
        [data-theme-mode="dark"] .form-control,
        [data-theme-mode="dark"] .form-select {
            background-color: var(--custom-white, #1f2937) !important;
            color: var(--default-text-color, #e5e7eb) !important;
            border-color: var(--default-border, #374151) !important;
        }

        [data-theme-mode="dark"] .table > :not(caption) > * > * {
            background-color: transparent !important;
            color: var(--default-text-color, #e5e7eb) !important;
            border-color: var(--default-border, #374151) !important;
        }

        [data-theme-mode="dark"] .text-muted,
        [data-theme-mode="dark"] .form-label {
            color: var(--text-muted, #9ca3af) !important;
        }

        .side-menu__item.active,
        .side-menu__item:hover {
            color: #0f4c81 !important;
            background-color: rgba(15, 76, 129, .08) !important;
        }

        .btn-primary,
        .bg-primary {
            background-color: #0f4c81 !important;
            border-color: #0f4c81 !important;
        }

        .text-primary {
            color: #0f4c81 !important;
        }
    </style>

</head>
