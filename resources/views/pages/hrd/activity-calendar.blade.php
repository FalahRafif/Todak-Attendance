@extends('layouts.admin.admin')
@section('title', $title)
@include('pages.hrd.partials.mobile-styles')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/main.min.css') }}">
<style>
    .ka-activity-shell{max-width:1100px;margin:0 auto}.ka-activity-legend{display:flex;gap:.75rem;flex-wrap:wrap}.ka-activity-legend span{display:inline-flex;align-items:center;gap:.4rem;color:#64748b;font-size:.85rem}.ka-dot{width:10px;height:10px;border-radius:999px;display:inline-block}.ka-calendar-card{border-radius:22px;overflow:hidden}.fc .fc-toolbar-title{font-size:1.25rem;font-weight:800;color:#0f172a}.fc .fc-button-primary{background:#0f4c81;border-color:#0f4c81}.fc .fc-daygrid-event{border-radius:999px;padding:.12rem .45rem;font-size:.75rem}@media(max-width:576px){.fc .fc-toolbar{flex-direction:column;gap:.75rem;align-items:stretch}.fc .fc-toolbar-chunk{display:flex;justify-content:center}.fc .fc-toolbar-title{font-size:1.05rem}.fc .fc-button{padding:.35rem .55rem;font-size:.78rem}}
</style>
@endpush
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-activity-shell">
    <div class="ka-hrd-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Kalender aktivitas non-absensi: cuti, sakit, izin, dan hari libur.</p></div></div>
    <div class="card custom-card ka-card mb-3"><div class="card-body"><form class="row g-2 align-items-end"><div class="col-6 col-md-4"><label class="form-label">Tanggal Mulai</label><input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="form-control"></div><div class="col-6 col-md-4"><label class="form-label">Tanggal Selesai</label><input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-control"></div><div class="col-12 col-md-4"><button class="btn btn-primary w-100">Filter Max 3 Bulan</button></div></form></div></div>
    <div class="ka-activity-legend mb-3"><span><i class="ka-dot" style="background:#8b5cf6"></i>Cuti Tahunan</span><span><i class="ka-dot" style="background:#ef4444"></i>Sakit</span><span><i class="ka-dot" style="background:#f59e0b"></i>Izin</span><span><i class="ka-dot" style="background:#0f4c81"></i>Libur</span></div>
    <div class="card custom-card ka-card ka-calendar-card"><div class="card-body"><div id="activity-calendar"></div></div></div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('assets/libs/fullcalendar/main.min.js') }}"></script>
<script src="{{ asset('assets/libs/fullcalendar/locales-all.min.js') }}"></script>
<script>
function initActivityCalendar(){
    var el=document.getElementById('activity-calendar');
    if(!el||typeof FullCalendar==='undefined')return;
    var calendar=new FullCalendar.Calendar(el,{locale:'id',initialView:window.innerWidth<576?'listWeek':'dayGridMonth',height:'auto',headerToolbar:{left:'prev,next today',center:'title',right:'dayGridMonth,listWeek'},buttonText:{today:'Hari ini',month:'Bulan',list:'Daftar'},events:@json($events),eventClick:function(info){info.jsEvent.preventDefault();var p=info.event.extendedProps||{};var msg=info.event.title;if(p.department)msg+='\nDepartemen: '+p.department;if(p.leaveType)msg+='\nJenis: '+p.leaveType;if(p.reason)msg+='\nCatatan: '+p.reason;alert(msg);}});
    calendar.render();
}
if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',initActivityCalendar)}else{initActivityCalendar()}
</script>
@endpush
