@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2></div><a href="{{ route('employee.leave-requests.create') }}" class="btn btn-primary">Create</a></div>
<div class="card custom-card ka-card"><div class="card-body p-0"><table class="table ka-table"><thead><tr><th>Type</th><th>Period</th><th>Days</th><th>Status</th><th></th></tr></thead><tbody>@forelse($items as $item)<tr><td>{{ $item->leaveType?->description ?? '-' }}</td><td>{{ $item->start_date?->format('Y-m-d') }} - {{ $item->end_date?->format('Y-m-d') }}</td><td>{{ $item->total_days }}</td><td>{{ $item->status?->description ?? '-' }}</td><td><a href="{{ route('employee.leave-requests.show', $item->id) }}" class="btn btn-sm btn-primary">Detail</a></td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No data</td></tr>@endforelse</tbody></table></div><div class="card-footer bg-transparent">{{ $items->links() }}</div></div>
@endsection
