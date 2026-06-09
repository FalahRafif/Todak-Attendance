@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Monitoring saldo cuti employee.</p></div></div>
<div class="card custom-card ka-card"><div class="card-body p-0"><div class="table-responsive"><table class="table ka-table"><thead><tr><th>Employee</th><th>Department</th><th>Type</th><th>Year</th><th>Quota</th><th>Used</th><th>Remaining</th></tr></thead><tbody>@forelse($items as $item)<tr><td>{{ $item->employee?->full_name ?? '-' }}</td><td>{{ $item->employee?->department?->name ?? '-' }}</td><td>{{ $item->leaveType?->description ?? '-' }}</td><td>{{ $item->year }}</td><td>{{ $item->total_quota }}</td><td>{{ $item->used_quota }}</td><td>{{ $item->remaining_quota }}</td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-4">No data</td></tr>@endforelse</tbody></table></div></div><div class="card-footer bg-transparent">{{ $items->links() }}</div></div>
@endsection
