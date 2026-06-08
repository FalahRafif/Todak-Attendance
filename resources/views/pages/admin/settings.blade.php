@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@php
    $actions = [
        ['label' => 'Verifikasi DP', 'url' => panel_route('admin.bookings.list', ['status' => 'BS_APPROVED_WAITING_DP']), 'class' => 'btn btn-outline-primary btn-sm'],
        ['label' => 'Verifikasi Final', 'url' => panel_route('admin.bookings.list', ['status' => 'BS_APPROVED_WAITING_FINAL_PAYMENT']), 'class' => 'btn btn-primary btn-sm'],
    ];

    $alerts = [
        ['class' => 'alert-info', 'text' => 'Halaman ini menampilkan konfigurasi operasional aktif. Untuk mengubah nilai, gunakan halaman kelola masing-masing group.'],
    ];

    $columns = $columns ?? ['Kategori', 'Item', 'Nilai', 'Tipe Paket', 'Kode'];
    $rows = $rows ?? [];
    $sideCards = $sideCards ?? [];
@endphp

@include('pages.admin.partials.page-header', [
    'heading' => 'Settings',
    'summary' => 'Konfigurasi dasar operasional dan kebijakan agar flow admin tetap konsisten dengan kebutuhan bisnis.',
    'actions' => $actions,
])

@include('pages.admin.partials.alerts', ['alerts' => $alerts])

<div class="row g-3">
    <div class="col-12 col-xl-8">
        @include('pages.admin.partials.data-table', [
            'tableTitle' => 'Konfigurasi Operasional',
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
