@extends('layouts.admin.admin')
@section('title', $title)
@include('pages.hrd.partials.mobile-styles')
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-hrd-shell">
    <div class="ka-hrd-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Pantau absensi, pengajuan, dan koreksi hari ini.</p></div></div>
    <div class="row g-3 mb-4">
        @foreach([['Karyawan',$totalEmployees],['Absen Hari Ini',$todayAttendances],['Terlambat',$lateAttendances],['Luar Radius',$outsideRadiusAttendances],['Izin/Cuti Menunggu',$pendingLeaves],['Koreksi Menunggu',$pendingCorrections]] as $stat)
            <div class="col-6 col-lg-2"><div class="card ka-hrd-stat"><div class="card-body"><span>{{ $stat[0] }}</span><h3>{{ $stat[1] }}</h3></div></div></div>
        @endforeach
    </div>
    <div class="card custom-card ka-card">
        <div class="ka-card-header"><h5 class="mb-0">Absensi Hari Ini</h5><a href="{{ route('hrd.attendances') }}" class="btn btn-sm btn-primary">Lihat Semua</a></div>
        <div class="card-body">
            <div class="ka-hrd-card-list">
                @forelse($recentAttendances as $item)
                    <div class="ka-hrd-item"><div class="d-flex justify-content-between gap-2"><div><div class="ka-hrd-item-title">{{ $item->employee?->full_name ?? '-' }}</div><div class="ka-hrd-meta">{{ $item->employee?->department?->name ?? '-' }}</div></div><span class="ka-hrd-pill">{{ friendly_label($item->status?->description) }}</span></div><div class="ka-hrd-meta mt-2">Masuk {{ $item->check_in_at?->format('H:i') ?? '-' }} · Pulang {{ $item->check_out_at?->format('H:i') ?? '-' }} · Telat {{ $item->late_minutes }} menit</div></div>
                @empty
                    <div class="text-center text-muted py-4">Belum ada data.</div>
                @endforelse
            </div>
            <div class="table-responsive ka-hrd-table"><table class="table ka-table"><thead><tr><th>Karyawan</th><th>Departemen</th><th>Masuk</th><th>Pulang</th><th>Telat</th><th>Status</th></tr></thead><tbody>@forelse($recentAttendances as $item)<tr><td>{{ $item->employee?->full_name ?? '-' }}</td><td>{{ $item->employee?->department?->name ?? '-' }}</td><td>{{ $item->check_in_at?->format('H:i') ?? '-' }}</td><td>{{ $item->check_out_at?->format('H:i') ?? '-' }}</td><td>{{ $item->late_minutes }} menit</td><td>{{ friendly_label($item->status?->description) }}</td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">Belum ada data.</td></tr>@endforelse</tbody></table></div>
        </div>
    </div>
</div>
@endsection
