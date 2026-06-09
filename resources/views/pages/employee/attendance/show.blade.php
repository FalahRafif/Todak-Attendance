@extends('layouts.admin.admin')
@section('title', $title)
@push('styles')
<style>
    .ka-detail-shell { max-width: 920px; margin: 0 auto; }
    .ka-insight-card { border: 1px solid #e5edf7; border-radius: 18px; padding: 1rem; background: #fff; height: 100%; }
    .ka-insight-card span { color: #64748b; font-size: .82rem; }
    .ka-insight-card h4 { margin: .35rem 0 0; }
    .ka-status-note { border-radius: 18px; padding: 1rem; }
</style>
@endpush
@section('content')
@php($lateTolerance = $item->shift?->late_tolerance_minutes ?? 0)
@php($isLateOutsideTolerance = $item->late_minutes > $lateTolerance)
@php($workHours = intdiv((int) $item->total_work_minutes, 60))
@php($workMinutes = (int) $item->total_work_minutes % 60)
@php($insideCheckIn = $item->check_in_is_inside_radius !== false)
@php($insideCheckOut = $item->check_out_is_inside_radius !== false)
<div class="ka-detail-shell">
    <div class="ka-toolbar">
        <div>
            <h2 class="ka-page-title">{{ $title }}</h2>
            <p class="ka-page-subtitle">Detail performa absensi dan informasi lokasi.</p>
        </div>
        <a href="{{ route('employee.attendance.history') }}" class="btn btn-light">Kembali</a>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6"><div class="ka-insight-card"><span>Tanggal</span><h4>{{ $item->attendance_date?->format('d M Y') }}</h4></div></div>
        <div class="col-md-3 col-6"><div class="ka-insight-card"><span>Jam kerja</span><h4>{{ $item->check_out_at ? $workHours.'j '.$workMinutes.'m' : 'Belum selesai' }}</h4></div></div>
        <div class="col-md-3 col-6"><div class="ka-insight-card"><span>Keterlambatan</span><h4 class="{{ $isLateOutsideTolerance ? 'text-danger' : 'text-success' }}">{{ $item->late_minutes > 0 ? $item->late_minutes.' menit' : '0 menit' }}</h4></div></div>
        <div class="col-md-3 col-6"><div class="ka-insight-card"><span>Status</span><h4>{{ friendly_label($item->status?->description) }}</h4></div></div>
    </div>
    <div class="ka-status-note mb-3 {{ $isLateOutsideTolerance ? 'bg-danger-transparent text-danger' : 'bg-success-transparent text-success' }}">
        @if($item->late_minutes > 0)
            Anda telat {{ $item->late_minutes }} menit. Toleransi shift {{ $lateTolerance }} menit, {{ $isLateOutsideTolerance ? 'melewati batas toleransi.' : 'masih dalam batas toleransi.' }}
        @else
            Anda absen masuk tepat waktu.
        @endif
        @if(!$item->check_out_at)
            <div>Absen pulang belum tercatat, jam kerja belum selesai.</div>
        @endif
    </div>
    <div class="card custom-card ka-card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><b>Absen Masuk</b><div>{{ $item->check_in_at?->format('Y-m-d H:i') ?? '-' }}</div></div>
                <div class="col-md-3"><b>Absen Pulang</b><div>{{ $item->check_out_at?->format('Y-m-d H:i') ?? '-' }}</div></div>
                <div class="col-md-3"><b>Shift</b><div>{{ $item->shift?->name ?? '-' }}</div></div>
                <div class="col-md-3"><b>Lokasi Kerja</b><div>{{ $item->workLocation?->name ?? '-' }}</div></div>
                <div class="col-md-6"><b>Absen Masuk GPS</b><div>{{ $item->check_in_latitude ?? '-' }}, {{ $item->check_in_longitude ?? '-' }} · {{ $insideCheckIn ? 'Dalam radius' : 'Luar radius' }} · {{ $item->check_in_distance_meter ?? '-' }} m</div></div>
                <div class="col-md-6"><b>Absen Pulang GPS</b><div>{{ $item->check_out_latitude ?? '-' }}, {{ $item->check_out_longitude ?? '-' }} · {{ $insideCheckOut ? 'Dalam radius' : 'Luar radius' }} · {{ $item->check_out_distance_meter ?? '-' }} m</div></div>
                <div class="col-md-6"><b>Absen Masuk Note</b><div>{{ $item->check_in_note ?? '-' }}</div></div>
                <div class="col-md-6"><b>Absen Pulang Note</b><div>{{ $item->check_out_note ?? '-' }}</div></div>
                <div class="col-md-6"><b>Pulang Lebih Awal</b><div>{{ $item->early_leave_minutes }} menit</div></div>
                <div class="col-md-6"><b>Pengecekan HRD</b><div>{{ $item->is_need_approval ? 'Perlu dicek karena lokasi di luar radius atau ada catatan khusus' : 'Tidak perlu dicek ulang' }}</div></div>
            </div>
        </div>
    </div>
</div>
@endsection
