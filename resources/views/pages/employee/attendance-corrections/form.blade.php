@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2></div><a href="{{ route('employee.attendance-corrections') }}" class="btn btn-light">Back</a></div>
<div class="card custom-card ka-card"><div class="card-body"><form method="POST" action="{{ route('employee.attendance-corrections.store') }}" class="row g-3">@csrf<div class="col-md-4"><label class="form-label">Correction Date</label><input type="date" name="correction_date" class="form-control" required></div><div class="col-md-4"><label class="form-label">Requested Check-in</label><input type="datetime-local" name="requested_check_in_at" class="form-control"></div><div class="col-md-4"><label class="form-label">Requested Check-out</label><input type="datetime-local" name="requested_check_out_at" class="form-control"></div><div class="col-12"><label class="form-label">Reason</label><textarea name="reason" class="form-control" required></textarea></div><div class="col-12"><button class="btn btn-primary">Save</button></div></form></div></div>
@endsection
