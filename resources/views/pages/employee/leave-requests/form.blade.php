@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2></div><a href="{{ route('employee.leave-requests') }}" class="btn btn-light">Back</a></div>
<div class="card custom-card ka-card"><div class="card-body"><form method="POST" action="{{ route('employee.leave-requests.store') }}" class="row g-3">@csrf<div class="col-md-4"><label class="form-label">Type</label><select name="leave_type_id" class="form-control" required>@foreach($leaveTypes as $type)<option value="{{ $type->id }}">{{ $type->description }}</option>@endforeach</select></div><div class="col-md-4"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" required></div><div class="col-md-4"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" required></div><div class="col-12"><label class="form-label">Reason</label><textarea name="reason" class="form-control" required></textarea></div><div class="col-12"><button class="btn btn-primary">Save</button></div></form></div></div>
@endsection
