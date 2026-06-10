@extends('layouts.admin.admin')
@section('title', $title)
@include('pages.hrd.partials.mobile-styles')
@section('content')
@include('pages.admin.modules.partials.flash')
<style>
    .ka-map-shell{display:grid;grid-template-columns:1fr 360px;gap:1rem}.ka-attendance-map{height:72vh;min-height:520px;border-radius:18px;overflow:hidden;border:1px solid #e5edf7}.ka-map-panel{max-height:72vh;overflow:auto}.ka-map-item{border:1px solid #e5edf7;border-radius:16px;padding:.85rem;margin-bottom:.75rem;background:#fff;cursor:pointer}.ka-map-item:hover{border-color:#2563eb;box-shadow:0 12px 24px rgba(37,99,235,.08)}.ka-map-pill{display:inline-flex;border-radius:999px;padding:.2rem .55rem;font-size:.75rem;font-weight:700}.ka-map-pill.in{background:#dcfce7;color:#166534}.ka-map-pill.out{background:#fee2e2;color:#991b1b}.ka-map-legend{display:flex;gap:.5rem;flex-wrap:wrap}.ka-dot{display:inline-block;width:10px;height:10px;border-radius:999px;margin-right:.25rem}.ka-dot.office{background:#2563eb}.ka-dot.in{background:#16a34a}.ka-dot.out{background:#dc2626}.ka-dot.out2{background:#f59e0b}@media(max-width:992px){.ka-map-shell{grid-template-columns:1fr}.ka-attendance-map{height:62vh;min-height:420px}.ka-map-panel{max-height:none}}
</style>
<div class="ka-hrd-shell">
    <div class="ka-hrd-toolbar">
        <div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Lihat titik absen karyawan dan bandingkan dengan lokasi kantor.</p></div>
        <a href="{{ route('hrd.attendances', request()->query()) }}" class="btn btn-light">List Absensi</a>
    </div>
    <div class="card custom-card ka-card mb-3"><div class="card-body"><form class="row g-2 ka-hrd-filter"><div class="col-6 col-md-2"><input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control"></div><div class="col-6 col-md-2"><select name="employee_id" class="form-control"><option value="">Karyawan</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected((int) request('employee_id') === $employee->id)>{{ $employee->full_name }}</option>@endforeach</select></div><div class="col-6 col-md-2"><select name="department_id" class="form-control"><option value="">Departemen</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((int) request('department_id') === $department->id)>{{ $department->name }}</option>@endforeach</select></div><div class="col-6 col-md-2"><select name="work_location_id" class="form-control"><option value="">Lokasi Kerja</option>@foreach($workLocations as $location)<option value="{{ $location->id }}" @selected((int) request('work_location_id') === $location->id)>{{ $location->name }}</option>@endforeach</select></div><div class="col-6 col-md-2"><select name="shift_id" class="form-control"><option value="">Shift</option>@foreach($shifts as $shift)<option value="{{ $shift->id }}" @selected((int) request('shift_id') === $shift->id)>{{ $shift->name }}</option>@endforeach</select></div><div class="col-6 col-md-2"><button class="btn btn-primary w-100">Filter</button></div></form></div></div>
    <div class="ka-map-legend mb-2"><span><i class="ka-dot office"></i>Kantor</span><span><i class="ka-dot in"></i>Masuk/Pulang dalam radius</span><span><i class="ka-dot out"></i>Di luar radius</span><span><i class="ka-dot out2"></i>Perlu approval</span></div>
    <div class="ka-map-shell">
        <div id="attendance-map" class="ka-attendance-map"></div>
        <div class="ka-map-panel">
            <div class="card custom-card ka-card"><div class="card-body"><h5 class="mb-2">Titik Absensi</h5><p class="text-muted mb-3">{{ $points->count() }} titik dari {{ $items->count() }} absensi pada {{ $date->format('d M Y') }}.</p>@forelse($points as $index => $point)<div class="ka-map-item" data-index="{{ $index }}"><div class="d-flex justify-content-between gap-2"><div><strong>{{ $point['employee_name'] }}</strong><div class="small text-muted">{{ $point['department'] }} · {{ $point['type_label'] }} {{ $point['time'] }}</div></div><span class="ka-map-pill {{ $point['inside'] ? 'in' : 'out' }}">{{ $point['inside'] ? 'Dalam Radius' : 'Luar Radius' }}</span></div><div class="small text-muted mt-2">{{ $point['work_location'] }} · {{ $point['distance'] ?? '-' }} m</div><a href="{{ $point['detail_url'] }}" class="btn btn-sm btn-light mt-2">Detail Absensi</a></div>@empty<div class="text-center text-muted py-4">Belum ada titik GPS absensi.</div>@endforelse</div></div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function(){
    var offices=@json($offices);
    var points=@json($points);
    var map=L.map('attendance-map');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'OSM'}).addTo(map);
    var bounds=[];
    var markers=[];
    offices.forEach(function(o){
        bounds.push([o.lat,o.lng]);
        L.circle([o.lat,o.lng],{radius:o.radius,color:'#2563eb',fillColor:'#2563eb',fillOpacity:.08,weight:2}).addTo(map);
        L.marker([o.lat,o.lng]).bindPopup('<b>Kantor: '+o.name+'</b><br>Radius '+o.radius+' m').addTo(map);
    });
    points.forEach(function(p,i){
        var color=p.need_approval?'#f59e0b':(p.inside?'#16a34a':'#dc2626');
        var marker=L.circleMarker([p.lat,p.lng],{radius:9,color:color,fillColor:color,fillOpacity:.9,weight:2}).addTo(map);
        var html='<b>'+p.employee_name+'</b><br>'+p.type_label+' '+p.time+'<br>'+p.work_location+'<br>Jarak: '+(p.distance ?? '-')+' m<br>Status: '+p.status+'<br><a href="'+p.detail_url+'">Detail Absensi</a>';
        marker.bindPopup(html);
        markers[i]=marker;
        bounds.push([p.lat,p.lng]);
        if(p.office_lat&&p.office_lng){L.polyline([[p.office_lat,p.office_lng],[p.lat,p.lng]],{color:color,dashArray:'5 8',weight:2,opacity:.75}).addTo(map)}
    });
    if(bounds.length){map.fitBounds(bounds,{padding:[30,30]})}else{map.setView([-6.1754,106.8272],12)}
    document.querySelectorAll('.ka-map-item').forEach(function(el){el.addEventListener('click',function(e){if(e.target.tagName==='A')return;var idx=parseInt(el.dataset.index);if(markers[idx]){map.setView(markers[idx].getLatLng(),17);markers[idx].openPopup()}})});
})();
</script>
@endsection
