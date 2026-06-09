@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2></div><a href="{{ route('employee.leave-requests') }}" class="btn btn-light">Back</a></div>
<div class="card custom-card ka-card"><div class="card-body"><p><b>Type:</b> {{ $item->leaveType?->description ?? '-' }}</p><p><b>Period:</b> {{ $item->start_date?->format('Y-m-d') }} - {{ $item->end_date?->format('Y-m-d') }}</p><p><b>Status:</b> {{ $item->status?->description ?? '-' }}</p><p><b>Reason:</b> {{ $item->reason }}</p><p><b>Rejected Reason:</b> {{ $item->rejected_reason ?? '-' }}</p><form method="POST" action="{{ route('employee.leave-requests.cancel', $item->id) }}">@csrf<button class="btn btn-danger">Cancel Pending Request</button></form></div></div>
@endsection
