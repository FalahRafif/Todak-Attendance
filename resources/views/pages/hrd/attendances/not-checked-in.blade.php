@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Employee aktif yang belum check-in pada tanggal terpilih.</p></div></div>
<div class="card custom-card ka-card mb-3"><div class="card-body"><form class="row g-2"><div class="col-md-3"><input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control"></div><div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div></form></div></div>
<div class="card custom-card ka-card"><div class="card-body p-0"><div class="table-responsive"><table class="table ka-table"><thead><tr><th>Employee</th><th>Department</th><th>Position</th><th>Work Location</th><th>Shift</th></tr></thead><tbody>@forelse($items as $item)<tr><td>{{ $item->full_name }}</td><td>{{ $item->department?->name ?? '-' }}</td><td>{{ $item->position?->name ?? '-' }}</td><td>{{ $item->workLocation?->name ?? '-' }}</td><td>{{ $item->shift?->name ?? '-' }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No data</td></tr>@endforelse</tbody></table></div></div><div class="card-footer bg-transparent">{{ $items->links() }}</div></div>
@endsection
