@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="d-flex justify-content-between align-items-center mb-3"><h2>{{ $title }}</h2><a href="{{ route('admin.employees.create') }}" class="btn btn-primary">Create</a></div>
<div class="card custom-card"><div class="card-body table-responsive"><table class="table table-bordered"><thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Employee Number</th><th>Department</th><th>Position</th><th>Status</th><th width="160">Action</th></tr></thead><tbody>@foreach($items as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->username }}</td><td>{{ $item->email }}</td><td>{{ $item->role?->name ?? '-' }}</td><td>{{ $item->employee?->employee_number ?? '-' }}</td><td>{{ $item->employee?->department?->name ?? '-' }}</td><td>{{ $item->employee?->position?->name ?? '-' }}</td><td>{{ $item->delete_status ? 'Deleted' : 'Active' }}</td><td><a href="{{ route('admin.employees.edit', $item->id) }}" class="btn btn-sm btn-primary">Edit</a><form method="POST" action="{{ route('admin.employees.destroy', $item->id) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form></td></tr>@endforeach</tbody></table>{{ $items->links() }}</div></div>
@endsection
