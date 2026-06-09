@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Approval koreksi absensi.</p></div></div>
<div class="card custom-card ka-card"><div class="card-body p-0"><div class="table-responsive"><table class="table ka-table"><thead><tr><th>Employee</th><th>Date</th><th>Requested In</th><th>Requested Out</th><th>Status</th><th></th></tr></thead><tbody>@forelse($items as $item)<tr><td>{{ $item->employee?->full_name ?? '-' }}</td><td>{{ $item->correction_date?->format('Y-m-d') }}</td><td>{{ $item->requested_check_in_at?->format('H:i') ?? '-' }}</td><td>{{ $item->requested_check_out_at?->format('H:i') ?? '-' }}</td><td>{{ $item->status?->description ?? '-' }}</td><td><a href="{{ route('hrd.attendance-corrections.show', $item->id) }}" class="btn btn-sm btn-primary">Detail</a></td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">No data</td></tr>@endforelse</tbody></table></div></div><div class="card-footer bg-transparent">{{ $items->links() }}</div></div>
@endsection
