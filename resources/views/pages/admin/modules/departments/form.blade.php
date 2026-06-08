@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
@php($isEdit = $item !== null)
<div class="mb-3"><h2>{{ $title }}</h2></div>
<div class="card custom-card"><div class="card-body"><form method="POST" action="{{ $isEdit ? route('admin.departments.update', $item->id) : route('admin.departments.store') }}" class="row g-3">@csrf @if($isEdit) @method('PUT') @endif
<div class="col-md-6"><label class="form-label">Name</label><input name="name" value="{{ old('name', $item?->name) }}" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Parent</label><select name="parent_id" class="form-control"><option value="">-</option>@foreach($parents as $parent)@if(!$isEdit || $parent->id !== $item->id)<option value="{{ $parent->id }}" @selected((int) old('parent_id', $item?->parent_id) === $parent->id)>{{ $parent->name }}</option>@endif @endforeach</select></div>
<div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control">{{ old('description', $item?->description) }}</textarea></div>
<div class="col-12"><button class="btn btn-primary">Save</button><a href="{{ route('admin.departments') }}" class="btn btn-light">Back</a></div>
</form></div></div>
@endsection
