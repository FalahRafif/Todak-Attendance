@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Monitoring, approval, dan report absensi HRD.</p></div></div>
<div class="row g-3 mb-4">
@foreach([['Employees',$totalEmployees],['Today Attendances',$todayAttendances],['Late',$lateAttendances],['Outside Radius',$outsideRadiusAttendances],['Pending Leaves',$pendingLeaves],['Pending Corrections',$pendingCorrections]] as $stat)
<div class="col-md-2"><div class="card custom-card ka-card"><div class="card-body"><span class="text-muted small">{{ $stat[0] }}</span><h3 class="mb-0">{{ $stat[1] }}</h3></div></div></div>
@endforeach
</div>
<div class="card custom-card ka-card"><div class="ka-card-header"><h5 class="mb-0">Today's Attendance</h5><a href="{{ route('hrd.attendances') }}" class="btn btn-sm btn-primary">View All</a></div><div class="card-body p-0"><div class="table-responsive"><table class="table ka-table"><thead><tr><th>Employee</th><th>Department</th><th>Check In</th><th>Check Out</th><th>Late</th><th>Status</th></tr></thead><tbody>@forelse($recentAttendances as $item)<tr><td>{{ $item->employee?->full_name ?? '-' }}</td><td>{{ $item->employee?->department?->name ?? '-' }}</td><td>{{ $item->check_in_at?->format('H:i') ?? '-' }}</td><td>{{ $item->check_out_at?->format('H:i') ?? '-' }}</td><td>{{ $item->late_minutes }} min</td><td>{{ $item->status?->description ?? '-' }}</td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">No data</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
