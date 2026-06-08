@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@php
    $actions = [
        ['label' => 'Booking Requests', 'url' => panel_route('admin.bookings.list', ['status' => 'BS_WAITING_APPROVAL']), 'class' => 'btn btn-outline-primary btn-sm'],
        ['label' => 'Verifikasi DP', 'url' => panel_route('admin.bookings.list', ['status' => 'BS_APPROVED_WAITING_DP']), 'class' => 'btn btn-outline-primary btn-sm'],
        ['label' => 'Calendar & Slots', 'url' => panel_route('admin.calendar'), 'class' => 'btn btn-primary btn-sm'],
    ];

    $alerts = [
        [
            'class' => 'alert-warning',
            'text' => 'Reminder: slot hanya dianggap terblokir jika status booking sudah active/paid setelah DP terverifikasi.',
        ],
    ];

    $stats = $stats ?? [];
    $columns = $columns ?? ['Kode', 'Customer', 'Tanggal Acara', 'Status Booking', 'Status Payment', 'Tindak Lanjut'];
    $rows = $rows ?? [];
    $sideCards = $sideCards ?? [];
@endphp

@include('pages.admin.partials.page-header', [
    'heading' => 'Dashboard',
    'summary' => 'Ringkasan operasional harian untuk booking flow, verifikasi pembayaran, dan kapasitas slot.',
    'actions' => $actions,
])

@include('pages.admin.partials.alerts', ['alerts' => $alerts])
@include('pages.admin.partials.stats-grid', ['stats' => $stats])

<div class="row g-3">
    <div class="col-12 col-xl-8">
        @include('pages.admin.partials.data-table', [
            'tableTitle' => 'Antrian Operasional',
            'tableBadge' => 'Live',
            'columns' => $columns,
            'rows' => $rows,
        ])
    </div>
    <div class="col-12 col-xl-4">
        @include('pages.admin.partials.side-cards', ['cards' => $sideCards])
    </div>
</div>
@endsection

