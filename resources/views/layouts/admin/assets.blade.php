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

        .ka-page-title {
            font-size: 1.55rem;
            font-weight: 700;
            letter-spacing: -.02em;
            margin-bottom: .25rem;
        }

        .ka-page-subtitle {
            color: #64748b;
            margin-bottom: 0;
        }

        .ka-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .ka-card {
            border: 1px solid #e5edf7;
            border-radius: 18px;
            box-shadow: 0 10px 28px rgba(15, 76, 129, .06);
            overflow: hidden;
        }

        .ka-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 1.25rem;
            background: linear-gradient(135deg, rgba(15, 76, 129, .08), rgba(15, 76, 129, .01));
            border-bottom: 1px solid #e5edf7;
        }

        .ka-search {
            max-width: 320px;
            border-radius: 999px;
            padding-left: 1rem;
        }

        .ka-table {
            margin-bottom: 0;
        }

        .ka-table thead th {
            background-color: #f8fbff;
            color: #334155;
            font-size: .76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1px solid #e5edf7;
            white-space: nowrap;
        }

        .ka-table tbody td {
            vertical-align: middle;
            color: #0f172a;
            border-color: #edf2f7;
        }

        .ka-table tbody tr:hover td {
            background-color: rgba(15, 76, 129, .035);
        }

        .ka-avatar {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #0f4c81;
            background: rgba(15, 76, 129, .1);
        }

        .ka-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .32rem .65rem;
            font-size: .75rem;
            font-weight: 700;
        }

        .ka-badge-primary {
            color: #0f4c81;
            background: rgba(15, 76, 129, .1);
        }

        .ka-badge-success {
            color: #047857;
            background: rgba(16, 185, 129, .12);
        }

        .ka-badge-muted {
            color: #64748b;
            background: #f1f5f9;
        }

        .ka-action-group {
            display: inline-flex;
            gap: .4rem;
        }

        .ka-action-group .btn {
            border-radius: 10px;
            padding: .38rem .7rem;
            font-weight: 700;
        }

        .ka-form-card .card-body {
            padding: 1.5rem;
        }

        .ka-form-section {
            padding: 1rem;
            border: 1px solid #e5edf7;
            border-radius: 14px;
            background: #fbfdff;
            margin-bottom: 1rem;
        }

        .ka-form-section-title {
            font-size: .82rem;
            font-weight: 800;
            color: #0f4c81;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 1rem;
        }

        .ka-form-card .form-control,
        .ka-form-card .form-select {
            border-radius: 12px;
            min-height: 42px;
        }

        [data-theme-mode="dark"] .ka-card {
            border-color: #334155;
            box-shadow: none;
        }

        [data-theme-mode="dark"] .ka-card-header,
        [data-theme-mode="dark"] .ka-form-section,
        [data-theme-mode="dark"] .ka-table thead th {
            background: rgba(15, 23, 42, .8);
            border-color: #334155;
            color: #e5e7eb;
        }

        [data-theme-mode="dark"] .ka-table tbody td {
            color: #e5e7eb;
            border-color: #334155;
        }

        [data-theme-mode="dark"] .ka-page-subtitle {
            color: #9ca3af;
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
