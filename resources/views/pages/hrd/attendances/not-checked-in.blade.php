@extends('layouts.admin.admin')
@section('title', $title)
@include('pages.hrd.partials.mobile-styles')
@section('content')
<div class="ka-hrd-shell">
    <div class="ka-hrd-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Karyawan aktif yang belum absen masuk pada tanggal terpilih.</p></div></div>
    <div class="card custom-card ka-card mb-3"><div class="card-body"><form class="row g-2 ka-hrd-filter"><div class="col-8 col-md-3"><input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control"></div><div class="col-4 col-md-2"><button class="btn btn-primary w-100">Filter</button></div></form></div></div>
    <div class="card custom-card ka-card"><div class="card-body"><div class="ka-hrd-card-list">@forelse($items as $item)<div class="ka-hrd-item"><div class="ka-hrd-item-title">{{ $item->full_name }}</div><div class="ka-hrd-meta">{{ $item->department?->name ?? '-' }} · {{ $item->position?->name ?? '-' }}</div><div class="ka-hrd-meta mt-2">{{ $item->workLocation?->name ?? '-' }} · {{ $item->shift?->name ?? '-' }}</div></div>@empty<div class="text-center text-muted py-4">Semua karyawan sudah absen masuk.</div>@endforelse</div><div class="table-responsive ka-hrd-table"><table class="table ka-table"><thead><tr><th>Karyawan</th><th>Departemen</th><th>Jabatan</th><th>Lokasi Kerja</th><th>Shift</th></tr></thead><tbody>@forelse($items as $item)<tr><td>{{ $item->full_name }}</td><td>{{ $item->department?->name ?? '-' }}</td><td>{{ $item->position?->name ?? '-' }}</td><td>{{ $item->workLocation?->name ?? '-' }}</td><td>{{ $item->shift?->name ?? '-' }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">Semua karyawan sudah absen masuk.</td></tr>@endforelse</tbody></table></div></div><div class="card-footer bg-transparent">{{ $items->links() }}</div></div>
</div>
@endsection
