@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@include('pages.admin.modules.partials.flash')
<div class="page-header-breadcrumb mb-3"><h2 class="main-content-title fs-24 mb-1">{{ $title }}</h2></div>
<div class="card custom-card"><div class="card-body"><form method="POST" action="{{ route('admin.departments.store') }}" class="row g-3">@csrf
<div class="col-md-4"><input name="name" class="form-control" placeholder="Name" required></div>
<div class="col-md-3"><select name="parent_id" class="form-control"><option value="">Parent</option>@foreach($parents as $parent)<option value="{{ $parent->id }}">{{ $parent->name }}</option>@endforeach</select></div>
<div class="col-md-4"><input name="description" class="form-control" placeholder="Description"></div>
<div class="col-md-1"><button class="btn btn-primary w-100">Save</button></div>
</form></div></div>
<div class="card custom-card"><div class="card-body table-responsive"><table class="table table-bordered"><thead><tr><th>Name</th><th>Parent</th><th>Description</th><th width="160">Action</th></tr></thead><tbody>@foreach($items as $item)<tr><form method="POST" action="{{ route('admin.departments.update', $item->id) }}">@csrf @method('PUT')<td><input name="name" value="{{ $item->name }}" class="form-control" required></td><td><select name="parent_id" class="form-control"><option value="">Parent</option>@foreach($parents as $parent)@if($parent->id !== $item->id)<option value="{{ $parent->id }}" @selected($item->parent_id === $parent->id)>{{ $parent->name }}</option>@endif @endforeach</select></td><td><input name="description" value="{{ $item->description }}" class="form-control"></td><td><button class="btn btn-sm btn-primary">Update</button></form><form method="POST" action="{{ route('admin.departments.destroy', $item->id) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form></td></tr>@endforeach</tbody></table>{{ $items->links() }}</div></div>
@endsection
