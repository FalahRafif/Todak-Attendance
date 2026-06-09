@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2></div><a href="{{ route('employee.attendance-corrections') }}" class="btn btn-light">Back</a></div>
<div class="card custom-card ka-card"><div class="card-body"><p><b>Date:</b> {{ $item->correction_date?->format('Y-m-d') }}</p><p><b>Requested In:</b> {{ $item->requested_check_in_at?->format('Y-m-d H:i') ?? '-' }}</p><p><b>Requested Out:</b> {{ $item->requested_check_out_at?->format('Y-m-d H:i') ?? '-' }}</p><p><b>Status:</b> {{ $item->status?->description ?? '-' }}</p><p><b>Reason:</b> {{ $item->reason }}</p><p><b>Rejected Reason:</b> {{ $item->rejected_reason ?? '-' }}</p><form method="POST" action="{{ route('employee.attendance-corrections.cancel', $item->id) }}">@csrf<button class="btn btn-danger">Cancel Pending Request</button></form></div></div>
@endsection
