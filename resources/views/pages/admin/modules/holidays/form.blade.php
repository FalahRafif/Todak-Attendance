@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
@php($isEdit = $item !== null)
<h2 class="mb-3">{{ $title }}</h2><div class="card custom-card"><div class="card-body"><form method="POST" action="{{ $isEdit ? route('admin.holidays.update', $item->id) : route('admin.holidays.store') }}" class="row g-3">@csrf @if($isEdit) @method('PUT') @endif
<div class="col-md-6"><label class="form-label">Name</label><input name="name" value="{{ old('name', $item?->name) }}" class="form-control" required></div><div class="col-md-6"><label class="form-label">Date</label><input type="date" name="holiday_date" value="{{ old('holiday_date', $item?->holiday_date?->format('Y-m-d')) }}" class="form-control" required></div><div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control">{{ old('description', $item?->description) }}</textarea></div><div class="col-md-3"><label><input type="checkbox" name="is_national_holiday" value="1" @checked(old('is_national_holiday', $item?->is_national_holiday))> National</label></div><div class="col-md-3"><label><input type="checkbox" name="is_company_holiday" value="1" @checked(old('is_company_holiday', $item?->is_company_holiday))> Company</label></div><div class="col-12"><button class="btn btn-primary">Save</button><a href="{{ route('admin.holidays') }}" class="btn btn-light">Back</a></div></form></div></div>
@endsection
