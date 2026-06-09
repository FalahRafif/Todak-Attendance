@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">History default bulan berjalan.</p></div></div>
<div class="card custom-card ka-card mb-3"><div class="card-body"><form class="row g-2"><div class="col-md-3"><input type="month" name="month" value="{{ $month->format('Y-m') }}" class="form-control"></div><div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div></form></div></div>
<div class="card custom-card ka-card"><div class="card-body p-0"><table class="table ka-table"><thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th></th></tr></thead><tbody>@forelse($items as $item)<tr><td>{{ $item->attendance_date?->format('Y-m-d') }}</td><td>{{ $item->check_in_at?->format('H:i') ?? '-' }}</td><td>{{ $item->check_out_at?->format('H:i') ?? '-' }}</td><td>{{ $item->status?->description ?? '-' }}</td><td><a href="{{ route('employee.attendance.history.show', $item->id) }}" class="btn btn-sm btn-primary">Detail</a></td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No data</td></tr>@endforelse</tbody></table></div><div class="card-footer bg-transparent">{{ $items->links() }}</div></div>
@endsection
