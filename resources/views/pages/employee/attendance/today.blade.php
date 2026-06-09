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
                <div class="text-end"><div class="text-muted">Status Persetujuan</div><h5 class="text-white mb-0">{{ $todayAttendance?->is_need_approval ? 'Perlu dicek HRD' : 'Normal' }}</h5></div>
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
</div>
@endsection
