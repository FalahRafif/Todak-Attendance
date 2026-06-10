@extends('layouts.admin.admin')
@section('title', $title)
@push('styles')
<style>
    .ka-mobile-shell { max-width: 920px; margin: 0 auto; }
    .ka-hero-card { border-radius: 28px; background: linear-gradient(135deg, #0f4c81, #2563eb); color: #fff; overflow: hidden; }
    .ka-hero-card .text-muted { color: rgba(255,255,255,.72) !important; }
    .ka-status-pill { display: inline-flex; align-items: center; gap: .5rem; border-radius: 999px; padding: .45rem .8rem; background: rgba(255,255,255,.16); font-weight: 700; }
    .ka-action-tile { display: flex; align-items: center; justify-content: space-between; gap: 1rem; border: 1px solid #e5edf7; border-radius: 20px; padding: 1rem; background: #fff; box-shadow: 0 10px 28px rgba(15,76,129,.06); }
    .ka-action-tile strong { display: block; color: #0f172a; }
    .ka-action-tile span { color: #64748b; font-size: .86rem; }
    .ka-action-tile.is-disabled { opacity: .58; pointer-events: none; background: #f8fafc; }
    .ka-timeline-item { display: flex; gap: .85rem; padding: .9rem 0; border-bottom: 1px solid #eef2f7; }
    .ka-timeline-dot { width: 12px; height: 12px; border-radius: 999px; background: #0f4c81; margin-top: .35rem; flex: 0 0 auto; }
    @media (max-width: 576px) { .ka-toolbar { align-items: flex-start; } .ka-hero-card { border-radius: 22px; } .ka-action-tile { border-radius: 18px; } .ka-action-tile .btn { white-space: nowrap; } }
</style>
@endpush
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-mobile-shell">
    <div class="ka-toolbar">
        <div>
            <h2 class="ka-page-title">{{ $title }}</h2>
            <p class="ka-page-subtitle">Ringkasan absensi hari ini dan pengajuan Anda.</p>
        </div>
    </div>
    <div class="card custom-card ka-card ka-hero-card mb-3">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between gap-3 flex-wrap">
                <div>
                    <div class="text-muted mb-2">{{ now()->translatedFormat('l, d M Y') }}</div>
                    <h3 class="mb-2 text-white">{{ $employee->full_name }}</h3>
                    <div class="ka-status-pill text-white">{{ $todayAttendance ? friendly_label($todayAttendance?->status?->description) : 'Belum masuk kerja' }}</div>
                </div>
                <div class="text-end">
                    <div class="text-muted">Shift</div>
                    <h5 class="mb-0 text-white">{{ $employee->shift?->name ?? '-' }}</h5>
                    <div class="text-muted mt-2">{{ $employee->workLocation?->name ?? '-' }}</div>
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-6"><div class="text-muted">Absen Masuk</div><h4 class="text-white mb-0">{{ $todayAttendance?->check_in_at?->format('H:i') ?? '--:--' }}</h4></div>
                <div class="col-6"><div class="text-muted">Absen Pulang</div><h4 class="text-white mb-0">{{ $todayAttendance?->check_out_at?->format('H:i') ?? '--:--' }}</h4></div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-6"><a href="{{ route('employee.attendance.check-in') }}" class="ka-action-tile text-decoration-none {{ $todayAttendance ? 'is-disabled' : '' }}"><div><strong>{{ $todayAttendance ? 'Sudah absen masuk' : 'Absen Masuk sekarang' }}</strong><span>{{ $todayAttendance ? 'Absen Masuk hari ini sudah tercatat' : 'Ambil selfie dan GPS lokasi' }}</span></div><span class="btn btn-primary">{{ $todayAttendance ? 'Selesai' : 'Mulai' }}</span></a></div>
        <div class="col-md-6"><a href="{{ route('employee.attendance.check-out') }}" class="ka-action-tile text-decoration-none {{ (!$todayAttendance || $todayAttendance->check_out_at) ? 'is-disabled' : '' }}"><div><strong>{{ $todayAttendance?->check_out_at ? 'Sudah absen pulang' : 'Absen Pulang' }}</strong><span>{{ $todayAttendance?->check_out_at ? 'Absen Pulang hari ini sudah tercatat' : 'Selesaikan jam kerja hari ini' }}</span></div><span class="btn btn-outline-primary">{{ $todayAttendance?->check_out_at ? 'Selesai' : 'Buka' }}</span></a></div>
        <div class="col-md-6"><a href="{{ route('employee.attendance.calendar') }}" class="ka-action-tile text-decoration-none"><div><strong>Kalender Absensi</strong><span>Lihat pola absen bulanan dan mingguan</span></div><span class="btn btn-light">Buka</span></a></div>
        <div class="col-md-6"><a href="{{ route('employee.leave-requests') }}" class="ka-action-tile text-decoration-none"><div><strong>Pengajuan Izin/Cuti</strong><span>{{ $pendingLeaves }} menunggu persetujuan</span></div><span class="btn btn-light">Lihat</span></a></div>
        <div class="col-md-6"><a href="{{ route('employee.attendance-corrections') }}" class="ka-action-tile text-decoration-none"><div><strong>Koreksi Absensi</strong><span>{{ $pendingCorrections }} menunggu persetujuan</span></div><span class="btn btn-light">Lihat</span></a></div>
        @if($leaveBalance)
        <div class="col-md-6"><a href="{{ route('employee.leave-requests.create') }}" class="ka-action-tile text-decoration-none"><div><strong>Sisa Cuti Tahunan</strong><span>{{ $leaveBalance->remaining_quota }} hari dari {{ $leaveBalance->total_quota }} hari · Terpakai {{ $leaveBalance->used_quota }}</span></div><span class="btn btn-light">Ajukan</span></a></div>
        @endif
    </div>
    <div class="card custom-card ka-card">
        <div class="ka-card-header"><h5 class="mb-0">Riwayat minggu ini</h5><a href="{{ route('employee.attendance.history') }}" class="btn btn-sm btn-light">Semua</a></div>
        <div class="card-body py-0">
            @forelse($monthlyAttendances as $item)
                @php($lateTolerance = $item->shift?->late_tolerance_minutes ?? 0)
                @php($isLateOutsideTolerance = $item->late_minutes > $lateTolerance)
                @php($workHours = intdiv((int) $item->total_work_minutes, 60))
                @php($workMinutes = (int) $item->total_work_minutes % 60)
                <a href="{{ route('employee.attendance.history.show', $item->id) }}" class="ka-timeline-item text-decoration-none">
                    <span class="ka-timeline-dot"></span>
                    <span class="flex-grow-1"><strong>{{ $item->attendance_date?->format('d M Y') }}</strong><span class="d-block text-muted">{{ $item->check_in_at?->format('H:i') ?? '--:--' }} - {{ $item->check_out_at?->format('H:i') ?? '--:--' }} · {{ $item->check_out_at ? $workHours.'j '.$workMinutes.'m kerja' : 'Belum absen pulang' }}</span><span class="d-block small {{ $isLateOutsideTolerance ? 'text-danger' : 'text-success' }}">{{ $item->late_minutes > 0 ? 'Telat '.$item->late_minutes.' menit'.($isLateOutsideTolerance ? ' (melewati toleransi)' : ' (masih toleransi)') : 'Tepat waktu' }}</span></span>
                    <span class="text-muted small">{{ friendly_label($item->status?->description) }}</span>
                </a>
            @empty
                <div class="text-center text-muted py-4">Belum ada riwayat.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
