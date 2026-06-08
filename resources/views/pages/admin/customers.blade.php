@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@php
    $actions = [
        ['label' => 'Booking Requests', 'url' => panel_route('admin.bookings.list', ['status' => 'BS_WAITING_APPROVAL']), 'class' => 'btn btn-outline-primary btn-sm'],
        ['label' => 'Dibatalkan', 'url' => panel_route('admin.bookings.list', ['status' => 'BS_CANCEL']), 'class' => 'btn btn-primary btn-sm'],
    ];

    $columns = $columns ?? ['Nama', 'WhatsApp', 'Email', 'Total Booking', 'Status Terakhir', 'Aksi'];
    $rows = $rows ?? [];
    $filters = $filters ?? [];
    $sideCards = $sideCards ?? [];
@endphp

@include('pages.admin.partials.page-header', [
    'heading' => 'Customers',
    'summary' => 'Rekap customer dan histori booking sebagai dasar koordinasi dan layanan lanjutan.',
    'actions' => $actions,
])

<div class="card custom-card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Filter Customer</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ panel_route('admin.customers') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4 col-xl-3">
                <label for="customer_name" class="form-label">Nama</label>
                <input type="text" id="customer_name" name="name" class="form-control" value="{{ $filters['name'] ?? '' }}" placeholder="Cari nama customer">
            </div>
            <div class="col-12 col-md-4 col-xl-3">
                <label for="customer_phone" class="form-label">Nomor Telepon</label>
                <input type="text" id="customer_phone" name="phone" class="form-control" value="{{ $filters['phone'] ?? '' }}" placeholder="08xxxxxxxxxx">
            </div>
            <div class="col-12 col-md-4 col-xl-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Cari</button>
                <a href="{{ panel_route('admin.customers') }}" class="btn btn-light w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        @include('pages.admin.partials.data-table', [
            'tableTitle' => 'Daftar Customer',
            'tableBadge' => 'Live',
            'columns' => $columns,
            'rows' => $rows,
            'emptyMessage' => 'Belum ada customer yang cocok dengan filter.',
        ])
    </div>
    <div class="col-12 col-xl-4">
        @include('pages.admin.partials.side-cards', ['cards' => $sideCards])
    </div>
</div>
@endsection
