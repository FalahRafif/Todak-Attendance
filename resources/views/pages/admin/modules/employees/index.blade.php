@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
@php($attachmentSecurity = app(\App\Services\AttachmentSecurityService::class))
<div class="ka-toolbar">
    <div>
        <h2 class="ka-page-title">{{ $title }}</h2>
        <p class="ka-page-subtitle">Kelola akun Admin, HRD, dan Employee dalam satu tempat.</p>
    </div>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary shadow-sm">Create User</a>
</div>
<div class="card custom-card ka-card">
    <div class="ka-card-header">
        <div>
            <h5 class="mb-1">User Directory</h5>
            <span class="text-muted">{{ $items->total() }} total data</span>
        </div>
        <input type="search" class="form-control ka-search" placeholder="Search user..." data-table-search="ka-user-table">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table ka-table" id="ka-user-table">
                <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Employee</th><th>Department</th><th>Position</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            @php($profileImageUrl = $attachmentSecurity->generateTemporaryPreviewUrl($item->profileImageAttachment))
                            <td><div class="d-flex align-items-center gap-3">@if($profileImageUrl)<img src="{{ $profileImageUrl }}" class="ka-avatar p-0" style="object-fit:cover" alt="{{ $item->name }}" onerror="this.outerHTML='<span class=&quot;ka-avatar&quot;>{{ strtoupper(substr($item->name ?: $item->username, 0, 1)) }}</span>'">@else<span class="ka-avatar">{{ strtoupper(substr($item->name ?: $item->username, 0, 1)) }}</span>@endif<div><div class="fw-semibold">{{ $item->name }}</div><div class="text-muted small">{{ $item->username }}</div></div></div></td>
                            <td>{{ $item->email }}</td>
                            <td><span class="ka-badge ka-badge-primary">{{ $item->role?->name ?? '-' }}</span></td>
                            <td><div class="fw-semibold">{{ $item->employee?->employee_number ?? '-' }}</div><div class="text-muted small">{{ $item->employee?->full_name ?? '' }}</div></td>
                            <td>{{ $item->employee?->department?->name ?? '-' }}</td>
                            <td>{{ $item->employee?->position?->name ?? '-' }}</td>
                            <td><span class="ka-badge {{ $item->delete_status ? 'ka-badge-muted' : 'ka-badge-success' }}">{{ $item->delete_status ? 'Deleted' : 'Active' }}</span></td>
                            <td class="text-end"><div class="ka-action-group"><a href="{{ route('admin.employees.edit', $item->id) }}" class="btn btn-sm btn-primary">Edit</a><form method="POST" action="{{ route('admin.employees.destroy', $item->id) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form></div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-transparent">{{ $items->links() }}</div>
</div>
@endsection
@push('scripts')@include('pages.admin.modules.partials.table-search')@endpush
