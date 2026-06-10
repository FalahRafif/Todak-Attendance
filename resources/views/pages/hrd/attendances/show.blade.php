@extends('layouts.admin.admin')
@section('title', $title)
@include('pages.hrd.partials.mobile-styles')
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-hrd-shell">
    <div class="ka-hrd-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Cek lokasi, catatan, perangkat, dan kebutuhan persetujuan.</p></div><a href="{{ route('hrd.attendances') }}" class="btn btn-light">Kembali</a></div>
    <div class="row g-3">
        <div class="col-lg-8"><div class="card custom-card ka-card"><div class="card-body"><h5>{{ $item->employee?->full_name ?? '-' }}</h5><p class="text-muted mb-3">{{ $item->employee?->department?->name ?? '-' }} · {{ $item->workLocation?->name ?? '-' }}</p><div class="row g-3"><div class="col-12 col-md-6"><div class="ka-detail-box"><b>Foto Masuk</b><div class="mt-2">@if($checkInPhotoUrl)<img src="{{ $checkInPhotoUrl }}" alt="Foto absen masuk" class="img-fluid rounded border">@else<span class="text-muted">Tidak ada foto.</span>@endif</div></div></div><div class="col-12 col-md-6"><div class="ka-detail-box"><b>Foto Pulang</b><div class="mt-2">@if($checkOutPhotoUrl)<img src="{{ $checkOutPhotoUrl }}" alt="Foto absen pulang" class="img-fluid rounded border">@else<span class="text-muted">Tidak ada foto.</span>@endif</div></div></div><div class="col-6 col-md-4"><div class="ka-detail-box"><b>Tanggal</b><div>{{ $item->attendance_date?->format('d M Y') }}</div></div></div><div class="col-6 col-md-4"><div class="ka-detail-box"><b>Absen Masuk</b><div>{{ $item->check_in_at?->format('Y-m-d H:i') ?? '-' }}</div></div></div><div class="col-6 col-md-4"><div class="ka-detail-box"><b>Absen Pulang</b><div>{{ $item->check_out_at?->format('Y-m-d H:i') ?? '-' }}</div></div></div><div class="col-12"><div class="ka-detail-box"><b>Peta Lokasi Masuk</b><div id="map-checkin" style="height:280px;border-radius:8px;margin-top:8px"></div><div class="mt-2"><small class="text-muted">GPS: {{ $item->check_in_latitude ?? '-' }}, {{ $item->check_in_longitude ?? '-' }} · Jarak: {{ $item->check_in_distance_meter ?? '-' }} m · @if($item->check_in_is_inside_radius)<span class="text-success">Di dalam radius</span>@else<span class="text-danger">Di luar radius</span>@endif</small></div></div></div><div class="col-12"><div class="ka-detail-box"><b>Peta Lokasi Pulang</b><div id="map-checkout" style="height:280px;border-radius:8px;margin-top:8px"></div><div class="mt-2"><small class="text-muted">GPS: {{ $item->check_out_latitude ?? '-' }}, {{ $item->check_out_longitude ?? '-' }} · Jarak: {{ $item->check_out_distance_meter ?? '-' }} m · @if($item->check_out_is_inside_radius)<span class="text-success">Di dalam radius</span>@else<span class="text-danger">Di luar radius</span>@endif</small></div></div></div><div class="col-12 col-md-6"><div class="ka-detail-box"><b>Catatan Masuk</b><div>{{ $item->check_in_note ?? '-' }}</div></div></div><div class="col-12 col-md-6"><div class="ka-detail-box"><b>Catatan Pulang</b><div>{{ $item->check_out_note ?? '-' }}</div></div></div><div class="col-12 col-md-6"><div class="ka-detail-box"><b>Perangkat Masuk</b><div class="small">{{ $item->check_in_device_info ?? '-' }}</div></div></div><div class="col-12 col-md-6"><div class="ka-detail-box"><b>Perangkat Pulang</b><div class="small">{{ $item->check_out_device_info ?? '-' }}</div></div></div></div></div></div></div>
        <div class="col-lg-4"><div class="card custom-card ka-card"><div class="card-body"><h5>Persetujuan HRD</h5><p>Perlu dicek: <b>{{ $item->is_need_approval ? 'Ya' : 'Tidak' }}</b></p><p>Catatan: {{ $item->approval_note ?? '-' }}</p><form method="POST" action="{{ route('hrd.attendances.approve', $item->id) }}" class="mb-2">@csrf<textarea name="approval_note" class="form-control mb-2" placeholder="Catatan persetujuan"></textarea><button class="btn btn-success w-100">Setujui</button></form><form method="POST" action="{{ route('hrd.attendances.reject', $item->id) }}">@csrf<textarea name="approval_note" class="form-control mb-2" placeholder="Alasan perlu ditandai" required></textarea><button class="btn btn-danger w-100">Tandai Perlu Perhatian</button></form></div></div></div>
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
