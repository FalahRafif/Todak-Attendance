@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Data profil karyawan.</p></div></div>
<div class="card custom-card ka-card"><div class="card-body"><div class="row g-3"><div class="col-md-4"><b>Name</b><div>{{ $employee->full_name }}</div></div><div class="col-md-4"><b>Email</b><div>{{ $employee->user?->email ?? '-' }}</div></div><div class="col-md-4"><b>Phone</b><div>{{ $employee->phone ?? '-' }}</div></div><div class="col-md-3"><b>Department</b><div>{{ $employee->department?->name ?? '-' }}</div></div><div class="col-md-3"><b>Position</b><div>{{ $employee->position?->name ?? '-' }}</div></div><div class="col-md-3"><b>Work Location</b><div>{{ $employee->workLocation?->name ?? '-' }}</div></div><div class="col-md-3"><b>Shift</b><div>{{ $employee->shift?->name ?? '-' }}</div></div></div></div></div>
@endsection
