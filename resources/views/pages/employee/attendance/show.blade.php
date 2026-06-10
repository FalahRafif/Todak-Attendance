@extends('layouts.admin.admin')
@section('title', $title)
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    .ka-detail-shell { max-width: 920px; margin: 0 auto; }
    .ka-insight-card { border: 1px solid #e5edf7; border-radius: 18px; padding: 1rem; background: #fff; height: 100%; }
    .ka-insight-card span { color: #64748b; font-size: .82rem; }
    .ka-insight-card h4 { margin: .35rem 0 0; }
    .ka-status-note { border-radius: 18px; padding: 1rem; }
    .ka-photo-box img { width: 100%; max-height: 360px; object-fit: cover; border-radius: 16px; border: 1px solid #e5edf7; }
    .ka-map-box { height: 320px; border-radius: 16px; overflow: hidden; border: 1px solid #e5edf7; background: #f8fafc; }
    @media (max-width: 576px) { .ka-map-box { height: 260px; } .ka-photo-box img { max-height: 260px; } }
</style>
@endpush
@section('content')
@php($lateTolerance = $item->shift?->late_tolerance_minutes ?? 0)
@php($isLateOutsideTolerance = $item->late_minutes > 0)
@php($workHours = intdiv((int) $item->total_work_minutes, 60))
@php($workMinutes = (int) $item->total_work_minutes % 60)
@php($insideCheckIn = $item->check_in_is_inside_radius !== false)
@php($insideCheckOut = $item->check_out_is_inside_radius !== false)
@php($isLeaveStatus = in_array($item->status?->description, ['leave', 'sick', 'permission'], true))
<div class="ka-detail-shell">
    <div class="ka-toolbar">
        <div>
            <h2 class="ka-page-title">{{ $title }}</h2>
            <p class="ka-page-subtitle">Detail performa absensi, foto selfie, dan komparasi lokasi kantor.</p>
        </div>
        <a href="{{ route('employee.attendance.history') }}" class="btn btn-light">Kembali</a>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6"><div class="ka-insight-card"><span>Tanggal</span><h4>{{ $item->attendance_date?->format('d M Y') }}</h4></div></div>
        <div class="col-md-3 col-6"><div class="ka-insight-card"><span>Jam kerja</span><h4>{{ $isLeaveStatus ? '-' : ($item->check_out_at ? $workHours.'j '.$workMinutes.'m' : 'Belum selesai') }}</h4></div></div>
        <div class="col-md-3 col-6"><div class="ka-insight-card"><span>Keterlambatan</span><h4 class="{{ $isLateOutsideTolerance ? 'text-danger' : 'text-success' }}">{{ $isLeaveStatus ? '-' : ($item->late_minutes > 0 ? $item->late_minutes.' menit' : '0 menit') }}</h4></div></div>
        <div class="col-md-3 col-6"><div class="ka-insight-card"><span>Status</span><h4>{{ friendly_label($item->status?->description) }}</h4></div></div>
    </div>
    <div class="ka-status-note mb-3 {{ $isLateOutsideTolerance ? 'bg-danger-transparent text-danger' : 'bg-success-transparent text-success' }}">
        @if($isLeaveStatus)
            Status hari ini adalah {{ friendly_label($item->status?->description) }}. Tidak ada kewajiban check-in/check-out.
        @elseif($item->late_minutes > 0)
            Anda telat {{ $item->late_minutes }} menit setelah toleransi shift {{ $lateTolerance }} menit.
        @else
            Anda absen masuk tepat waktu.
        @endif
        @if(!$isLeaveStatus && !$item->check_out_at)
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
    <div class="row g-3 mb-3">
        <div class="col-md-6"><div class="card custom-card ka-card h-100"><div class="card-body"><h5>Foto Masuk</h5><div class="ka-photo-box mt-2">@if($checkInPhotoUrl)<img src="{{ $checkInPhotoUrl }}" alt="Foto absen masuk">@else<div class="text-center text-muted py-5">Tidak ada foto masuk.</div>@endif</div></div></div></div>
        <div class="col-md-6"><div class="card custom-card ka-card h-100"><div class="card-body"><h5>Foto Pulang</h5><div class="ka-photo-box mt-2">@if($checkOutPhotoUrl)<img src="{{ $checkOutPhotoUrl }}" alt="Foto absen pulang">@else<div class="text-center text-muted py-5">Tidak ada foto pulang.</div>@endif</div></div></div></div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-12"><div class="card custom-card ka-card"><div class="card-body"><h5>Peta Lokasi Masuk</h5><div id="employee-map-checkin" class="ka-map-box mt-2"></div><div class="small text-muted mt-2">Kantor vs posisi absen masuk. Radius: {{ $item->workLocation?->radius_meter ?? 100 }} m.</div></div></div></div>
        <div class="col-12"><div class="card custom-card ka-card"><div class="card-body"><h5>Peta Lokasi Pulang</h5><div id="employee-map-checkout" class="ka-map-box mt-2"></div><div class="small text-muted mt-2">Kantor vs posisi absen pulang. Radius: {{ $item->workLocation?->radius_meter ?? 100 }} m.</div></div></div></div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var officeLat = {{ $item->workLocation?->latitude ?? 'null' }};
    var officeLng = {{ $item->workLocation?->longitude ?? 'null' }};
    var officeRadius = {{ $item->workLocation?->radius_meter ?? 100 }};
    function initMap(id, lat, lng, label, inside) {
        var el = document.getElementById(id);
        if (!el) return;
        if (!lat || !lng || !officeLat || !officeLng) { el.innerHTML = '<div class="text-center text-muted py-5">Data GPS belum tersedia.</div>'; return; }
        var map = L.map(id).setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: 'OSM' }).addTo(map);
        L.circle([officeLat, officeLng], { radius: officeRadius, color: '#2563eb', fillColor: '#2563eb', fillOpacity: .08, weight: 2 }).addTo(map);
        L.marker([officeLat, officeLng]).bindPopup('<b>Lokasi Kantor</b>').addTo(map);
        var color = inside ? '#16a34a' : '#dc2626';
        L.circleMarker([lat, lng], { radius: 9, color: color, fillColor: color, fillOpacity: .9, weight: 2 }).bindPopup('<b>' + label + '</b><br>' + lat + ', ' + lng).addTo(map);
        L.polyline([[officeLat, officeLng], [lat, lng]], { color: color, dashArray: '5 8', weight: 2 }).addTo(map);
        map.fitBounds([[officeLat, officeLng], [lat, lng]], { padding: [30, 30] });
    }
    initMap('employee-map-checkin', {{ $item->check_in_latitude ?? 'null' }}, {{ $item->check_in_longitude ?? 'null' }}, 'Posisi Masuk', {{ $insideCheckIn ? 'true' : 'false' }});
    initMap('employee-map-checkout', {{ $item->check_out_latitude ?? 'null' }}, {{ $item->check_out_longitude ?? 'null' }}, 'Posisi Pulang', {{ $insideCheckOut ? 'true' : 'false' }});
});
</script>
@endpush
