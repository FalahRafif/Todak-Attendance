@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="d-flex justify-content-between align-items-center mb-3"><h2>{{ $title }}</h2><a href="{{ route('admin.holidays.create') }}" class="btn btn-primary">Create</a></div>
<div class="card custom-card"><div class="card-body table-responsive"><table class="table table-bordered"><thead><tr><th>Name</th><th>Date</th><th>Description</th><th>Type</th><th width="160">Action</th></tr></thead><tbody>@foreach($items as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->holiday_date?->format('Y-m-d') }}</td><td>{{ $item->description ?? '-' }}</td><td>{{ $item->is_national_holiday ? 'National' : '' }} {{ $item->is_company_holiday ? 'Company' : '' }}</td><td><a href="{{ route('admin.holidays.edit', $item->id) }}" class="btn btn-sm btn-primary">Edit</a><form method="POST" action="{{ route('admin.holidays.destroy', $item->id) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form></td></tr>@endforeach</tbody></table>{{ $items->links() }}</div></div>
@endsection
