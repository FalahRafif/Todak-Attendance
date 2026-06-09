@extends('layouts.admin.admin')
@section('title', $title)
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/main.min.css') }}">
<style>
    .ka-calendar-shell { max-width: 1100px; margin: 0 auto; }
    .ka-calendar-card { border-radius: 22px; overflow: hidden; }
    #attendance-calendar { min-height: 650px; }
    .fc .fc-toolbar-title { font-size: 1.25rem; font-weight: 800; color: #0f172a; }
    .fc .fc-button-primary { background: #0f4c81; border-color: #0f4c81; }
    .fc .fc-daygrid-event { border-radius: 999px; padding: .12rem .45rem; font-size: .75rem; }
    .ka-calendar-legend { display: flex; gap: .75rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .ka-calendar-legend span { display: inline-flex; align-items: center; gap: .45rem; color: #64748b; font-size: .85rem; }
    .ka-calendar-dot { width: 10px; height: 10px; border-radius: 999px; display: inline-block; }
    .ka-event-panel { border: 1px solid #e5edf7; border-radius: 18px; padding: 1rem; background: #f8fbff; }
    @media (max-width: 576px) {
        #attendance-calendar { min-height: 560px; }
        .fc .fc-toolbar { flex-direction: column; gap: .75rem; align-items: stretch; }
        .fc .fc-toolbar-chunk { display: flex; justify-content: center; }
        .fc .fc-toolbar-title { font-size: 1.05rem; }
        .fc .fc-button { padding: .35rem .55rem; font-size: .78rem; }
        .fc .fc-daygrid-day-number { font-size: .78rem; }
        .fc .fc-daygrid-event { font-size: .68rem; }
    }
</style>
@endpush
@section('content')
<div class="ka-calendar-shell">
    <div class="ka-toolbar">
        <div>
            <h2 class="ka-page-title">{{ $title }}</h2>
            <p class="ka-page-subtitle">Lihat absensi bulan ini, minggu ini, dan bulan sebelumnya.</p>
        </div>
        <a href="{{ route('employee.attendance.history') }}" class="btn btn-light">Daftar Riwayat</a>
    </div>
    <div class="card custom-card ka-card mb-3"><div class="card-body"><form class="row g-2 align-items-end"><div class="col-6 col-md-4"><label class="form-label">Tanggal Mulai</label><input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="form-control"></div><div class="col-6 col-md-4"><label class="form-label">Tanggal Selesai</label><input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-control"></div><div class="col-12 col-md-4"><button class="btn btn-primary w-100">Filter Max 3 Bulan</button></div><div class="col-12"><div class="text-muted small">Default bulan ini. Filter otomatis dibatasi maksimal 3 bulan agar halaman tetap ringan.</div></div></form></div></div>
    <div class="ka-calendar-legend">
        <span><i class="ka-calendar-dot" style="background:#10b981"></i>Normal</span>
        <span><i class="ka-calendar-dot" style="background:#ef4444"></i>Telat melewati toleransi</span>
        <span><i class="ka-calendar-dot" style="background:#6366f1"></i>Belum absen pulang</span>
        <span><i class="ka-calendar-dot" style="background:#f59e0b"></i>Di luar radius / perlu dicek HRD</span>
        <span><i class="ka-calendar-dot" style="background:#dc2626"></i>Libur / tanggal merah</span>
        <span><i class="ka-calendar-dot" style="background:#8b5cf6"></i>Cuti / izin / sakit disetujui</span>
    </div>
    <div class="card custom-card ka-card ka-calendar-card mb-3">
        <div class="card-body">
            <div id="attendance-calendar"></div>
        </div>
    </div>
    <div class="ka-event-panel d-none" id="event-panel">
        <div class="d-flex justify-content-between gap-3 flex-wrap">
            <div>
                <strong id="event-title">Absensi</strong>
                <div class="text-muted small" id="event-time"></div>
                <div class="small mt-1" id="event-extra"></div>
            </div>
            <a href="#" class="btn btn-sm btn-primary" id="event-detail">Detail</a>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('assets/libs/fullcalendar/main.min.js') }}"></script>
<script src="{{ asset('assets/libs/fullcalendar/locales-all.min.js') }}"></script>
<script>
    function initAttendanceCalendar() {
        var calendarElement = document.getElementById('attendance-calendar');
        if (!calendarElement || typeof FullCalendar === 'undefined') {
            return;
        }
        var panel = document.getElementById('event-panel');
        var title = document.getElementById('event-title');
        var time = document.getElementById('event-time');
        var extra = document.getElementById('event-extra');
        var detail = document.getElementById('event-detail');
        var calendar = new FullCalendar.Calendar(calendarElement, {
            locale: 'id',
            initialView: window.innerWidth < 576 ? 'listWeek' : 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: {
                today: 'Hari ini',
                month: 'Bulan',
                week: 'Minggu',
                list: 'Daftar'
            },
            displayEventTime: true,
            displayEventEnd: true,
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            },
            slotLabelFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            },
            events: @json($events),
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.location.href = info.event.url;
                }
            }
        });
        calendar.render();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAttendanceCalendar);
    } else {
        initAttendanceCalendar();
    }
</script>
@endpush
