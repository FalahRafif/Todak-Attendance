@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@php
    $actions = [
        ['label' => 'Daftar Booking', 'url' => panel_route('admin.bookings.list'), 'class' => 'btn btn-outline-primary btn-sm'],
        ['label' => 'Booking Requests', 'url' => panel_route('admin.bookings.list', ['status' => 'BS_WAITING_APPROVAL']), 'class' => 'btn btn-outline-primary btn-sm'],
        ['label' => 'Booking Aktif', 'url' => panel_route('admin.bookings.list', ['status' => 'BS_CONFIRMED']), 'class' => 'btn btn-primary btn-sm'],
    ];

    $filters = $filters ?? ['status' => '', 'date_start' => '', 'date_end' => ''];
    $statusFilters = $statusFilters ?? [];
    $stats = $stats ?? [];
    $upcomingBookings = $upcomingBookings ?? [];
    $currentStatus = strtoupper(trim((string) ($filters['status'] ?? '')));

    $statusToneClass = [
        'primary' => 'bg-primary-transparent text-primary',
        'secondary' => 'bg-secondary-transparent text-secondary',
        'success' => 'bg-success-transparent text-success',
        'warning' => 'bg-warning-transparent text-warning',
        'danger' => 'bg-danger-transparent text-danger',
        'info' => 'bg-info-transparent text-info',
    ];
@endphp

@include('pages.admin.partials.page-header', [
    'heading' => 'Calender Booking',
    'summary' => 'Visualisasi jadwal booking untuk membantu petugas melihat status booking per tanggal, lalu lanjut review ke detail booking.',
    'actions' => $actions,
])

@include('pages.admin.partials.stats-grid', ['stats' => $stats])

<div class="row g-3">
    <div class="col-12 col-xxl-3">
        <div class="card custom-card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Filter Kalender</h5>
            </div>
            <div class="card-body">
                <form id="booking_calendar_filter_form" data-events-url="{{ panel_route('admin.calendar.events', [], false) }}">
                    <div class="mb-3">
                        <label class="form-label" for="calendar_status_filter">Status Booking</label>
                        <select id="calendar_status_filter" name="status" class="form-select">
                            @foreach($statusFilters as $statusFilter)
                                <option value="{{ $statusFilter['code'] }}" @selected($currentStatus === strtoupper((string) $statusFilter['code']))>
                                    {{ $statusFilter['label'] }} ({{ $statusFilter['count'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="calendar_date_start">Tanggal Mulai</label>
                        <input type="date" id="calendar_date_start" name="date_start" class="form-control" value="{{ $filters['date_start'] ?? '' }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="calendar_date_end">Tanggal Akhir</label>
                        <input type="date" id="calendar_date_end" name="date_end" class="form-control" value="{{ $filters['date_end'] ?? '' }}">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" id="booking_calendar_apply_filter" class="btn btn-primary">Terapkan Filter</button>
                        <button type="button" id="booking_calendar_reset_filter" class="btn btn-light">Reset Filter</button>
                    </div>
                </form>

                <div class="booking-calendar-status-pills mt-3" id="booking_calendar_status_pills">
                    @foreach($statusFilters as $statusFilter)
                        @php
                            $statusCode = strtoupper((string) ($statusFilter['code'] ?? ''));
                            $isActive = (bool) ($statusFilter['is_active'] ?? false);
                            $tone = (string) ($statusFilter['tone'] ?? 'secondary');
                            $toneClass = $statusToneClass[$tone] ?? $statusToneClass['secondary'];
                        @endphp
                        <button
                            type="button"
                            class="btn btn-sm {{ $toneClass }} booking-calendar-status-pill {{ $isActive ? 'is-active' : '' }}"
                            data-status-code="{{ $statusCode }}"
                        >
                            {{ $statusFilter['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card custom-card mb-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Booking Terdekat</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 booking-calendar-upcoming">
                    @forelse($upcomingBookings as $booking)
                        @php
                            $tone = (string) ($booking['tone'] ?? 'secondary');
                            $toneClass = $statusToneClass[$tone] ?? $statusToneClass['secondary'];
                        @endphp
                        <li class="booking-calendar-upcoming-item">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <p class="mb-1 fw-semibold">{{ $booking['case_id'] }}</p>
                                    <p class="mb-1 text-muted small">{{ $booking['customer'] }}</p>
                                    <p class="mb-0 text-muted small">{{ $booking['date'] }} • {{ $booking['session'] }}</p>
                                </div>
                                <span class="badge {{ $toneClass }}">{{ $booking['status_label'] }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="text-muted small">Belum ada booking terdekat pada filter saat ini.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-12 col-xxl-9">
        <div class="card custom-card mb-0">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="card-title mb-0">Visualisasi Calender Booking</h5>
                <span id="booking_calendar_active_filter" class="badge bg-primary-transparent text-primary">Semua Status</span>
            </div>
            <div class="card-body position-relative">
                <div id="booking_calendar_loading" class="booking-calendar-loading d-none">
                    <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
                    <span>Memuat data booking...</span>
                </div>

                <div id="booking_calendar" class="booking-calendar"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/main.min.css') }}">
<style>
    .booking-calendar {
        min-height: 680px;
    }
    .booking-calendar-loading {
        position: absolute;
        top: 1.2rem;
        right: 1.2rem;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        border: 1px solid #e8e8f0;
        border-radius: 999px;
        padding: 0.35rem 0.65rem;
        font-size: 0.8rem;
        color: #4d5875;
    }
    .booking-calendar-status-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }
    .booking-calendar-status-pill {
        border: 1px solid transparent;
    }
    .booking-calendar-status-pill.is-active {
        border-color: rgba(0, 0, 0, 0.22);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.4);
    }
    .booking-calendar-upcoming {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        max-height: 500px;
        overflow: auto;
        padding-right: 0.25rem;
    }
    .booking-calendar-upcoming-item {
        border: 1px solid #ececf2;
        border-radius: 0.75rem;
        padding: 0.65rem 0.75rem;
        background: #fff;
    }
    .booking-calendar .fc-toolbar-title {
        font-size: 1.35rem;
        font-weight: 600;
    }
    .booking-calendar .fc .fc-button-primary {
        background-color: #38cab3;
        border-color: #38cab3;
    }
    .booking-calendar .fc .fc-button-primary:hover,
    .booking-calendar .fc .fc-button-primary:focus {
        background-color: #2fb29e;
        border-color: #2fb29e;
    }
    .booking-calendar .fc .fc-event {
        cursor: pointer;
        border-radius: 0.35rem;
        border-width: 0;
        font-size: 0.72rem;
        padding: 0.12rem 0.32rem;
    }
    .booking-calendar .fc .fc-daygrid-event-dot {
        border-color: currentColor;
    }
    @media (max-width: 991.98px) {
        .booking-calendar {
            min-height: 560px;
        }
        .booking-calendar .fc-toolbar {
            gap: 0.5rem;
        }
        .booking-calendar .fc-toolbar.fc-header-toolbar {
            margin-bottom: 0.9rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/libs/fullcalendar/main.min.js') }}"></script>
<script src="{{ asset('assets/libs/fullcalendar/locales-all.min.js') }}"></script>
<script src="{{ asset('assets/js/fullcalendar.js') }}"></script>
@php
    $calendarBookingScriptPath = public_path('assets/pages/admin/bookings/calendar-booking.js');
    $calendarBookingScriptVersion = file_exists($calendarBookingScriptPath) ? filemtime($calendarBookingScriptPath) : time();
@endphp
<script src="{{ asset('assets/pages/admin/bookings/calendar-booking.js') }}?v={{ $calendarBookingScriptVersion }}"></script>
@endpush

