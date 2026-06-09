@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-mobile-shell" style="max-width:820px;margin:0 auto">
    <div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Detail koreksi absensi.</p></div><a href="{{ route('employee.attendance-corrections') }}" class="btn btn-light">Kembali</a></div>
    <div class="card custom-card ka-card"><div class="card-body p-4"><div class="d-flex justify-content-between gap-3 flex-wrap mb-3"><div><div class="text-muted small">Tanggal Koreksi</div><h4>{{ $item->correction_date?->format('d M Y') }}</h4></div><span class="ka-badge ka-badge-primary">{{ friendly_label($item->status?->description) }}</span></div><div class="row g-3"><div class="col-md-6"><b>Jam Masuk yang Diajukan</b><div>{{ $item->requested_check_in_at?->format('Y-m-d H:i') ?? '-' }}</div></div><div class="col-md-6"><b>Jam Pulang yang Diajukan</b><div>{{ $item->requested_check_out_at?->format('Y-m-d H:i') ?? '-' }}</div></div><div class="col-12"><b>Alasan</b><div>{{ $item->reason }}</div></div><div class="col-12"><b>Alasan Ditolak</b><div>{{ $item->rejected_reason ?? '-' }}</div></div></div>@if($item->status?->description === 'pending')<form method="POST" action="{{ route('employee.attendance-corrections.cancel', $item->id) }}" class="mt-4">@csrf<button class="btn btn-danger w-100">Batalkan Koreksi</button></form>@endif</div></div>
</div>
@endsection
