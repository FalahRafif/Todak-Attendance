@extends('layouts.admin.admin')
@section('title', $title)
@push('styles')
<style>
    .ka-mobile-shell { max-width: 820px; margin: 0 auto; }
    .ka-today-card { border-radius: 28px; border: 0; background: linear-gradient(135deg, #0f172a, #0f4c81); color: #fff; }
    .ka-today-card .text-muted { color: rgba(255,255,255,.7) !important; }
    .ka-time-box { background: rgba(255,255,255,.12); border-radius: 18px; padding: 1rem; height: 100%; }
    .ka-step-card { border: 1px solid #e5edf7; border-radius: 18px; padding: 1rem; background: #fff; }
    @media (max-width: 576px) { .ka-today-card { border-radius: 22px; } .ka-sticky-actions { position: sticky; bottom: 1rem; z-index: 5; } }
</style>
@endpush
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-mobile-shell">
    <div class="ka-toolbar">
        <div>
            <h2 class="ka-page-title">{{ $title }}</h2>
            <p class="ka-page-subtitle">Pantau status dan lanjutkan aksi absensi hari ini.</p>
        </div>
    </div>
    <div class="card custom-card ka-card ka-today-card mb-3">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between gap-3 flex-wrap mb-3">
                <div><div class="text-muted">Status hari ini</div><h3 class="text-white mb-0">{{ $todayAttendance ? friendly_label($todayAttendance?->status?->description) : 'Belum absen masuk' }}</h3></div>
                <div class="text-end"><div class="text-muted">Status Lokasi</div><h5 class="text-white mb-0">{{ $todayAttendance?->is_need_approval ? 'Perlu Review HRD' : 'Aman' }}</h5><div class="small text-white-50">@if($todayAttendance?->check_in_is_inside_radius === false)<span>Absen Masuk: Luar Radius</span><br>@endif @if($todayAttendance?->check_out_is_inside_radius === false)<span>Absen Pulang: Luar Radius</span>@endif</div></div>
            </div>
            <div class="row g-3">
                <div class="col-6"><div class="ka-time-box"><div class="text-muted">Absen Masuk</div><h2 class="text-white mb-0">{{ $todayAttendance?->check_in_at?->format('H:i') ?? '--:--' }}</h2></div></div>
                <div class="col-6"><div class="ka-time-box"><div class="text-muted">Absen Pulang</div><h2 class="text-white mb-0">{{ $todayAttendance?->check_out_at?->format('H:i') ?? '--:--' }}</h2></div></div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-6"><div class="ka-step-card"><strong>1. Absen Masuk</strong><p class="text-muted mb-3">Ambil selfie, GPS, lalu sistem cek radius lokasi kerja.</p><a href="{{ route('employee.attendance.check-in') }}" class="btn btn-primary w-100 {{ $todayAttendance ? 'disabled' : '' }}">{{ $todayAttendance ? 'Sudah absen masuk' : 'Mulai Absen Masuk' }}</a></div></div>
        <div class="col-md-6"><div class="ka-step-card"><strong>2. Absen Pulang</strong><p class="text-muted mb-3">Aktif setelah absen masuk dan belum absen pulang.</p><a href="{{ route('employee.attendance.check-out') }}" class="btn btn-outline-primary w-100 {{ (!$todayAttendance || $todayAttendance->check_out_at) ? 'disabled' : '' }}">{{ $todayAttendance?->check_out_at ? 'Sudah absen pulang' : 'Mulai Absen Pulang' }}</a></div></div>
    </div>
    <div class="ka-sticky-actions d-grid gap-2 d-md-none"><a href="{{ route('employee.attendance.history') }}" class="btn btn-light shadow-sm">Lihat Riwayat</a></div>
    @if($todayAttendance)
    <div class="card custom-card ka-card">
        <div class="card-body p-0">
            <a href="{{ route('employee.attendance.history.show', $todayAttendance->id) }}" class="d-block text-decoration-none text-reset p-3">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <div class="fw-bold">{{ friendly_label($todayAttendance->status?->description) }} · {{ $todayAttendance->attendance_date?->format('d M Y') }}</div>
                        <div class="text-muted small mt-1">{{ $todayAttendance->workLocation?->name ?? '-' }} · {{ $todayAttendance->shift?->name ?? '-' }}</div>
                        <div class="mt-2 d-flex gap-2 flex-wrap">
                            <span class="badge bg-primary-transparent text-primary">Masuk {{ $todayAttendance->check_in_at?->format('H:i') ?? '--:--' }}</span>
                            <span class="badge bg-secondary-transparent text-secondary">Pulang {{ $todayAttendance->check_out_at?->format('H:i') ?? '--:--' }}</span>
                            @if($todayAttendance->late_minutes > 0)<span class="badge bg-danger-transparent text-danger">Telat {{ $todayAttendance->late_minutes }}m</span>@else<span class="badge bg-success-transparent text-success">Tepat waktu</span>@endif
                            @if($todayAttendance->check_in_is_inside_radius === false)<span class="badge {{ $todayAttendance->check_in_review_status_id ? 'bg-info-transparent text-info' : 'bg-warning-transparent text-warning' }}">Masuk: {{ $todayAttendance->check_in_review_status_id ? 'Sudah Direview' : 'Luar Radius' }}</span>@endif
                            @if($todayAttendance->check_out_is_inside_radius === false)<span class="badge {{ $todayAttendance->check_out_review_status_id ? 'bg-info-transparent text-info' : 'bg-warning-transparent text-warning' }}">Pulang: {{ $todayAttendance->check_out_review_status_id ? 'Sudah Direview' : 'Luar Radius' }}</span>@endif
                        </div>
                    </div>
                    <div class="text-muted fs-4">›</div>
                </div>
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
