@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Absen hari ini.</p></div></div>
<div class="card custom-card ka-card"><div class="card-body"><div class="row g-3"><div class="col-md-3"><b>Status</b><div>{{ $todayAttendance?->status?->description ?? 'Belum check-in' }}</div></div><div class="col-md-3"><b>Check-in</b><div>{{ $todayAttendance?->check_in_at?->format('H:i') ?? '-' }}</div></div><div class="col-md-3"><b>Check-out</b><div>{{ $todayAttendance?->check_out_at?->format('H:i') ?? '-' }}</div></div><div class="col-md-3"><b>Need Approval</b><div>{{ $todayAttendance?->is_need_approval ? 'Yes' : 'No' }}</div></div></div><div class="mt-4 d-flex gap-2"><a href="{{ route('employee.attendance.check-in') }}" class="btn btn-primary">Check-in</a><a href="{{ route('employee.attendance.check-out') }}" class="btn btn-outline-primary">Check-out</a></div></div></div>
@endsection
