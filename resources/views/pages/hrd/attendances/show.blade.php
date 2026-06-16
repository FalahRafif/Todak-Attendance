@extends('layouts.admin.admin')
@section('title', $title)
@include('pages.hrd.partials.mobile-styles')
@section('content')
@include('pages.admin.modules.partials.flash')
@php
    $approvedId = \App\Models\Reference::query()->where('description', 'approved')->value('id');
    $rejectedId = \App\Models\Reference::query()->where('description', 'rejected')->value('id');
    $pendingId = \App\Models\Reference::query()->where('description', 'pending')->value('id');

    $reviewBadge = function (?int $statusId) use ($approvedId, $rejectedId): string {
        if ($statusId === null) return '<span class="badge bg-warning text-dark">Perlu Review</span>';
        if ($statusId === $approvedId) return '<span class="badge bg-success">Disetujui</span>';
        if ($statusId === $rejectedId) return '<span class="badge bg-danger">Ditandai</span>';
        return '<span class="badge bg-secondary">-</span>';
    };
    $reviewLabel = function (?int $statusId) use ($approvedId, $rejectedId): string {
        if ($statusId === null) return 'Perlu Review';
        if ($statusId === $approvedId) return 'Disetujui';
        if ($statusId === $rejectedId) return 'Ditandai Perlu Perhatian';
        return '-';
    };

    $checkInOutside = $item->check_in_at !== null && $item->check_in_is_inside_radius === false;
    $checkOutOutside = $item->check_out_at !== null && $item->check_out_is_inside_radius === false;
    $checkInReviewStatus = $item->check_in_review_status_id;
    $checkOutReviewStatus = $item->check_out_review_status_id;
    $checkInPending = $checkInOutside && $checkInReviewStatus === null;
    $checkOutPending = $checkOutOutside && $checkOutReviewStatus === null;
@endphp
<div class="ka-hrd-shell">
    <div class="ka-hrd-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Cek lokasi, catatan, perangkat, dan review outside radius per sesi.</p></div><a href="{{ route('hrd.attendances') }}" class="btn btn-light">Kembali</a></div>
    <div class="row g-3">
        <div class="col-lg-8"><div class="card custom-card ka-card"><div class="card-body"><h5>{{ $item->employee?->full_name ?? '-' }}</h5><p class="text-muted mb-3">{{ $item->employee?->department?->name ?? '-' }} · {{ $item->workLocation?->name ?? '-' }}</p><div class="row g-3"><div class="col-12 col-md-6"><div class="ka-detail-box"><b>Foto Masuk</b><div class="mt-2">@if($checkInPhotoUrl)<img src="{{ $checkInPhotoUrl }}" alt="Foto absen masuk" class="img-fluid rounded border">@else<span class="text-muted">Tidak ada foto.</span>@endif</div></div></div><div class="col-12 col-md-6"><div class="ka-detail-box"><b>Foto Pulang</b><div class="mt-2">@if($checkOutPhotoUrl)<img src="{{ $checkOutPhotoUrl }}" alt="Foto absen pulang" class="img-fluid rounded border">@else<span class="text-muted">Tidak ada foto.</span>@endif</div></div></div><div class="col-6 col-md-4"><div class="ka-detail-box"><b>Tanggal</b><div>{{ $item->attendance_date?->format('d M Y') }}</div></div></div><div class="col-6 col-md-4"><div class="ka-detail-box"><b>Absen Masuk</b><div>{{ $item->check_in_at?->format('Y-m-d H:i') ?? '-' }}</div></div></div><div class="col-6 col-md-4"><div class="ka-detail-box"><b>Absen Pulang</b><div>{{ $item->check_out_at?->format('Y-m-d H:i') ?? '-' }}</div></div></div><div class="col-12"><div class="ka-detail-box"><b>Peta Lokasi Masuk</b><div id="map-checkin" style="height:280px;border-radius:8px;margin-top:8px"></div><div class="mt-2"><small class="text-muted">GPS: {{ $item->check_in_latitude ?? '-' }}, {{ $item->check_in_longitude ?? '-' }} · Jarak: {{ $item->check_in_distance_meter ?? '-' }} m · @if($item->check_in_is_inside_radius === false)<span class="text-danger">Di luar radius</span>@elseif($item->check_in_is_inside_radius)<span class="text-success">Di dalam radius</span>@else<span class="text-muted">-</span>@endif · Work Mode: {{ $item->checkInWorkMode?->description ?? '-' }}</small></div></div></div><div class="col-12"><div class="ka-detail-box"><b>Peta Lokasi Pulang</b><div id="map-checkout" style="height:280px;border-radius:8px;margin-top:8px"></div><div class="mt-2"><small class="text-muted">GPS: {{ $item->check_out_latitude ?? '-' }}, {{ $item->check_out_longitude ?? '-' }} · Jarak: {{ $item->check_out_distance_meter ?? '-' }} m · @if($item->check_out_is_inside_radius === false)<span class="text-danger">Di luar radius</span>@elseif($item->check_out_is_inside_radius)<span class="text-success">Di dalam radius</span>@else<span class="text-muted">-</span>@endif · Work Mode: {{ $item->checkOutWorkMode?->description ?? '-' }}</small></div></div></div></div></div></div></div>

        <div class="col-lg-4">
            <div class="card custom-card ka-card mb-3">
                <div class="card-body">
                    <h5>Review Outside Radius</h5>
                    <p class="text-muted small">Review dilakukan per sesi (masuk / pulang). Tombol hanya tampil untuk sesi yang masih perlu review.</p>

                    @if(! $checkInOutside && ! $checkOutOutside)
                        <div class="alert alert-success mb-0">Tidak ada sesi outside radius pada absensi ini.</div>
                    @else

                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <b>Absen Masuk</b>
                                @if($checkInOutside) {!! $reviewBadge($checkInReviewStatus) !!} @else <span class="badge bg-secondary">Di dalam radius</span> @endif
                            </div>
                            @if($checkInOutside)
                                @if($checkInReviewStatus !== null)
                                    <div class="text-muted small">
                                        Status: <b>{{ $reviewLabel($checkInReviewStatus) }}</b><br>
                                        @if($item->check_in_reviewed_at) Diselesaikan: {{ $item->check_in_reviewed_at->format('d M Y H:i') }}<br>@endif
                                        @if($item->check_in_review_note) Catatan: {{ $item->check_in_review_note }}<br>@endif
                                    </div>
                                    <form method="POST" action="{{ route('hrd.attendances.review', $item->id) }}" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="session" value="check_in">
                                        <textarea name="note" class="form-control form-control-sm mb-2" placeholder="Catatan baru (ubah keputusan)..."></textarea>
                                        <div class="d-flex gap-1">
                                            <button type="submit" name="decision" value="approved" class="btn btn-sm btn-success flex-fill">Setujui Ulang</button>
                                            <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-danger flex-fill">Tandai Ulang</button>
                                        </div>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('hrd.attendances.review', $item->id) }}">
                                        @csrf
                                        <input type="hidden" name="session" value="check_in">
                                        <textarea name="note" class="form-control form-control-sm mb-2" placeholder="Catatan review absen masuk..."></textarea>
                                        <div class="d-flex gap-1">
                                            <button type="submit" name="decision" value="approved" class="btn btn-sm btn-success flex-fill">Setujui</button>
                                            <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-danger flex-fill">Tandai Perlu Perhatian</button>
                                        </div>
                                    </form>
                                @endif
                            @else
                                <div class="text-muted small">Sesi masuk dalam radius kantor. Tidak perlu review.</div>
                            @endif
                        </div>

                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <b>Absen Pulang</b>
                                @if($checkOutOutside) {!! $reviewBadge($checkOutReviewStatus) !!} @else <span class="badge bg-secondary">Di dalam radius</span> @endif
                            </div>
                            @if($checkOutOutside)
                                @if($checkOutReviewStatus !== null)
                                    <div class="text-muted small">
                                        Status: <b>{{ $reviewLabel($checkOutReviewStatus) }}</b><br>
                                        @if($item->check_out_reviewed_at) Diselesaikan: {{ $item->check_out_reviewed_at->format('d M Y H:i') }}<br>@endif
                                        @if($item->check_out_review_note) Catatan: {{ $item->check_out_review_note }}<br>@endif
                                    </div>
                                    <form method="POST" action="{{ route('hrd.attendances.review', $item->id) }}" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="session" value="check_out">
                                        <textarea name="note" class="form-control form-control-sm mb-2" placeholder="Catatan baru (ubah keputusan)..."></textarea>
                                        <div class="d-flex gap-1">
                                            <button type="submit" name="decision" value="approved" class="btn btn-sm btn-success flex-fill">Setujui Ulang</button>
                                            <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-danger flex-fill">Tandai Ulang</button>
                                        </div>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('hrd.attendances.review', $item->id) }}">
                                        @csrf
                                        <input type="hidden" name="session" value="check_out">
                                        <textarea name="note" class="form-control form-control-sm mb-2" placeholder="Catatan review absen pulang..."></textarea>
                                        <div class="d-flex gap-1">
                                            <button type="submit" name="decision" value="approved" class="btn btn-sm btn-success flex-fill">Setujui</button>
                                            <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-danger flex-fill">Tandai Perlu Perhatian</button>
                                        </div>
                                    </form>
                                @endif
                            @else
                                <div class="text-muted small">Sesi pulang dalam radius kantor. Tidak perlu review.</div>
                            @endif
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function(){
    var wlLat={{ $item->workLocation?->latitude ?? -6.1754 }};
    var wlLng={{ $item->workLocation?->longitude ?? 106.8272 }};
    var wlRad={{ $item->workLocation?->radius_meter ?? 100 }};

    function initMap(id,lat,lng,label){
        if(!lat||!lng)return;
        var map=L.map(id).setView([lat,lng],16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'OSM'}).addTo(map);
        L.circle([wlLat,wlLng],{radius:wlRad,color:'#3b82f6',fillOpacity:0.08,weight:2}).addTo(map);
        L.marker([wlLat,wlLng],{title:'Kantor'}).bindPopup('<b>Lokasi Kantor</b>').addTo(map);
        L.marker([lat,lng],{title:label}).bindPopup('<b>'+label+'</b><br>'+lat.toFixed(6)+', '+lng.toFixed(6)).addTo(map);
        L.polyline([[wlLat,wlLng],[lat,lng]],{color:'#ef4444',dashArray:'6 8'}).addTo(map);
    }
    @if($item->check_in_latitude && $item->check_in_longitude)
    initMap('map-checkin',{{ $item->check_in_latitude }},{{ $item->check_in_longitude }},'Posisi Masuk');
    @else
    document.getElementById('map-checkin').innerHTML='<div class="text-center text-muted py-5">Tidak ada data GPS masuk.</div>';
    @endif
    @if($item->check_out_latitude && $item->check_out_longitude)
    initMap('map-checkout',{{ $item->check_out_latitude }},{{ $item->check_out_longitude }},'Posisi Pulang');
    @else
    document.getElementById('map-checkout').innerHTML='<div class="text-center text-muted py-5">Tidak ada data GPS pulang.</div>';
    @endif
})();
</script>
@endsection
