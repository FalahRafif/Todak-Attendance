@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
<div class="page-header-breadcrumb mb-3"><h2 class="main-content-title fs-24 mb-1">{{ $title }}</h2></div>
<div class="row">
    @foreach ($stats as $label => $value)
        <div class="col-xl-3 col-md-6"><div class="card custom-card"><div class="card-body"><p class="mb-1 text-muted">{{ $label }}</p><h3 class="mb-0">{{ $value }}</h3></div></div></div>
    @endforeach
</div>
@endsection
