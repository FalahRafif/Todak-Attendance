@extends('layouts.admin.admin')
@section('title', $title)
@push('styles')
<style>.ka-mobile-shell{max-width:920px;margin:0 auto}.ka-request-card{display:block;border:1px solid #e5edf7;border-radius:20px;padding:1rem;background:#fff;text-decoration:none;color:inherit;box-shadow:0 8px 22px rgba(15,76,129,.05)}.ka-request-icon{width:52px;height:52px;border-radius:16px;display:flex;align-items:center;justify-content:center;background:#fff7ed;color:#f97316;font-weight:800;flex:0 0 auto}.ka-status-badge{border-radius:999px;padding:.3rem .7rem;font-size:.75rem;font-weight:700;background:#f1f5f9;color:#475569}</style>
@endpush
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-mobile-shell">
    <div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Ajukan koreksi jika jam absen masuk/pulang tidak sesuai.</p></div><a href="{{ route('employee.attendance-corrections.create') }}" class="btn btn-primary">Buat Koreksi</a></div>
    <div class="card custom-card ka-card mb-3"><div class="card-body"><form class="row g-2"><div class="col-6 col-md-4"><input type="month" name="month" value="{{ request('month') }}" class="form-control"></div><div class="col-6 col-md-4"><select name="status_id" class="form-control"><option value="">Semua Status</option>@foreach($statuses as $status)<option value="{{ $status->id }}" @selected((int) request('status_id') === $status->id)>{{ friendly_label($status->description) }}</option>@endforeach</select></div><div class="col-12 col-md-4"><button class="btn btn-primary w-100">Filter</button></div></form></div></div>
    <div class="d-grid gap-3">@forelse($items as $item)<a href="{{ route('employee.attendance-corrections.show', $item->id) }}" class="ka-request-card"><div class="d-flex gap-3 align-items-center"><div class="ka-request-icon">{{ $item->correction_date?->format('d') }}</div><div class="flex-grow-1"><div class="d-flex justify-content-between gap-2 flex-wrap"><strong>{{ $item->correction_date?->format('d M Y') }}</strong><span class="ka-status-badge">{{ friendly_label($item->status?->description) }}</span></div><div class="text-muted small mt-1">Diajukan: {{ $item->requested_check_in_at?->format('H:i') ?? '--:--' }} - {{ $item->requested_check_out_at?->format('H:i') ?? '--:--' }}</div><div class="small mt-2">{{ str($item->reason)->limit(90) }}</div></div><span class="text-muted">›</span></div></a>@empty<div class="card custom-card ka-card"><div class="card-body text-center text-muted py-5">Belum ada koreksi absensi.</div></div>@endforelse</div>
    <div class="mt-3">{{ $items->links() }}</div>
</div>
@endsection
