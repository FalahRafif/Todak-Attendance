@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Monitoring jadwal kerja employee.</p></div></div>
<div class="card custom-card ka-card"><div class="card-body p-0"><div class="table-responsive"><table class="table ka-table"><thead><tr><th>Date</th><th>Employee</th><th>Department</th><th>Shift</th><th>Note</th></tr></thead><tbody>@forelse($items as $item)<tr><td>{{ $item->schedule_date?->format('Y-m-d') ?? '-' }}</td><td>{{ $item->employee?->full_name ?? '-' }}</td><td>{{ $item->employee?->department?->name ?? '-' }}</td><td>{{ $item->shift?->name ?? '-' }}</td><td>{{ $item->note ?? '-' }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No data</td></tr>@endforelse</tbody></table></div></div><div class="card-footer bg-transparent">{{ $items->links() }}</div></div>
@endsection
