@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@php
    $actions = [
        ['label' => 'Booking Requests', 'url' => panel_route('admin.bookings.list', ['status' => 'BS_WAITING_APPROVAL']), 'class' => 'btn btn-outline-primary btn-sm'],
        ['label' => 'Booking Aktif', 'url' => panel_route('admin.bookings.list', ['status' => 'BS_CONFIRMED']), 'class' => 'btn btn-outline-primary btn-sm'],
        ['label' => 'Calendar & Slots', 'url' => panel_route('admin.calendar'), 'class' => 'btn btn-primary btn-sm'],
    ];
    $columns = ['Case ID', 'Customer', 'Tanggal Pengajuan', 'Tanggal Acara', 'Sesi', 'Paket', 'Lokasi', 'Status', 'Aksi'];
    $stats = $stats ?? [];
    $rows = $rows ?? [];
    $statusFilters = $statusFilters ?? [];
    $filters = $filters ?? [];
    $totalCount = $totalCount ?? 0;
    $filteredCount = $filteredCount ?? 0;
    $currentStatus = strtoupper(trim((string) ($filters['status'] ?? '')));
    $queryFilters = array_filter($filters, static fn ($value) => $value !== null && $value !== '');
@endphp

@include('pages.admin.partials.page-header', [
    'heading' => 'Daftar Booking',
    'summary' => 'Daftar seluruh booking dengan filter status, case ID, dan rentang tanggal.',
    'actions' => $actions,
])

@include('pages.admin.partials.stats-grid', ['stats' => $stats])

<div class="card custom-card mb-3">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="card-title mb-0">Filter Booking</h5>
        <span class="badge bg-primary-transparent text-primary">Menampilkan {{ $filteredCount }} dari {{ $totalCount }} booking</span>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach ($statusFilters as $filter)
                @php
                    $isActive = $filter['is_active'] ?? false;
                    $filterCode = trim((string) ($filter['code'] ?? ''));
                    $tone = (string) ($filter['tone'] ?? 'primary');
                    $tone = in_array($tone, ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'light', 'dark'], true)
                        ? $tone
                        : 'primary';
                    $toneBadgeClass = match ($tone) {
                        'light' => 'bg-light text-dark',
                        'dark' => 'bg-dark text-white',
                        default => 'bg-' . $tone . '-transparent text-' . $tone,
                    };
                    $activeBadgeClass = match ($tone) {
                        'light', 'dark' => 'bg-white text-dark',
                        default => 'bg-white text-' . $tone,
                    };
                    $badgeClass = $isActive ? $activeBadgeClass : $toneBadgeClass;
                    $baseFilters = $queryFilters;
                    unset($baseFilters['status']);
                    $targetFilters = $filterCode !== '' ? array_merge($baseFilters, ['status' => $filterCode]) : $baseFilters;
                @endphp
                <a href="{{ route('admin.bookings.list', $targetFilters) }}" class="btn btn-sm {{ $isActive ? 'btn-' . $tone : 'btn-outline-' . $tone }}">
                    {{ $filter['label'] ?? '-' }}
                    <span class="badge {{ $badgeClass }} ms-2">{{ $filter['count'] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.bookings.list') }}" class="row g-3 align-items-end" id="booking_filter_form">
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            <div class="col-12 col-md-4 col-xl-3">
                <label for="case_id" class="form-label">Case ID</label>
                <input type="text" id="case_id" name="case_id" class="form-control" value="{{ $filters['case_id'] ?? '' }}" placeholder="ETH-20260505-00001">
            </div>
            <div class="col-12 col-md-3 col-xl-2">
                <label for="date_range" class="form-label">Rentang Tanggal</label>
                <select id="date_range" name="date_range" class="form-select">
                    <option value="all" {{ ($filters['date_range'] ?? '') === 'all' || ($filters['date_range'] ?? '') === '' ? 'selected' : '' }}>Semua</option>
                    <option value="week" {{ ($filters['date_range'] ?? '') === 'week' ? 'selected' : '' }}>Mingguan</option>
                    <option value="month" {{ ($filters['date_range'] ?? '') === 'month' ? 'selected' : '' }}>Bulanan</option>
                    <option value="custom" {{ ($filters['date_range'] ?? '') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2" id="date_start_wrap">
                <label for="date_start" class="form-label">Tanggal Mulai</label>
                <input type="date" id="date_start" name="date_start" class="form-control" value="{{ $filters['date_start'] ?? '' }}">
            </div>
            <div class="col-6 col-md-3 col-xl-2" id="date_end_wrap">
                <label for="date_end" class="form-label">Tanggal Akhir</label>
                <input type="date" id="date_end" name="date_end" class="form-control" value="{{ $filters['date_end'] ?? '' }}">
            </div>
            <div class="col-12 col-md-3 col-xl-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
                <a href="{{ route('admin.bookings.list') }}" class="btn btn-light w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

@include('pages.admin.partials.data-table', [
    'tableTitle' => 'List Booking',
    'tableBadge' => 'All Status',
    'columns' => $columns,
    'rows' => $rows,
    'emptyMessage' => 'Belum ada booking yang cocok dengan filter.',
])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var dateRange = document.getElementById('date_range');
    var startWrap = document.getElementById('date_start_wrap');
    var endWrap = document.getElementById('date_end_wrap');
    if (!dateRange || !startWrap || !endWrap) return;

    function toggleDateInputs() {
        var isCustom = dateRange.value === 'custom';
        startWrap.style.display = isCustom ? '' : 'none';
        endWrap.style.display = isCustom ? '' : 'none';
        document.getElementById('date_start').disabled = !isCustom;
        document.getElementById('date_end').disabled = !isCustom;
    }

    dateRange.addEventListener('change', toggleDateInputs);
    toggleDateInputs();
});
</script>
@endpush