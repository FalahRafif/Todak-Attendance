@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
@php($isEdit = $item !== null)
<h2 class="mb-3">{{ $title }}</h2><div class="card custom-card"><div class="card-body"><form method="POST" action="{{ $isEdit ? route('admin.positions.update', $item->id) : route('admin.positions.store') }}" class="row g-3">@csrf @if($isEdit) @method('PUT') @endif
<div class="col-md-6"><label class="form-label">Name</label><input name="name" value="{{ old('name', $item?->name) }}" class="form-control" required></div><div class="col-md-6"><label class="form-label">Department</label><select name="department_id" class="form-control"><option value="">-</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((int) old('department_id', $item?->department_id) === $department->id)>{{ $department->name }}</option>@endforeach</select></div><div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control">{{ old('description', $item?->description) }}</textarea></div><div class="col-12"><button class="btn btn-primary">Save</button><a href="{{ route('admin.positions') }}" class="btn btn-light">Back</a></div></form></div></div>
@endsection
