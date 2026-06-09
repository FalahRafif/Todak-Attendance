@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2></div><a href="{{ route('employee.attendance-corrections.create') }}" class="btn btn-primary">Create</a></div>
<div class="card custom-card ka-card"><div class="card-body p-0"><table class="table ka-table"><thead><tr><th>Date</th><th>Requested In</th><th>Requested Out</th><th>Status</th><th></th></tr></thead><tbody>@forelse($items as $item)<tr><td>{{ $item->correction_date?->format('Y-m-d') }}</td><td>{{ $item->requested_check_in_at?->format('H:i') ?? '-' }}</td><td>{{ $item->requested_check_out_at?->format('H:i') ?? '-' }}</td><td>{{ $item->status?->description ?? '-' }}</td><td><a href="{{ route('employee.attendance-corrections.show', $item->id) }}" class="btn btn-sm btn-primary">Detail</a></td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No data</td></tr>@endforelse</tbody></table></div><div class="card-footer bg-transparent">{{ $items->links() }}</div></div>
@endsection
