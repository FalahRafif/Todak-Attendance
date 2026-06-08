@extends('layouts.admin.admin')

@section('title', $title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/pages/admin/location-pricing-rules/index.css') }}">
@endpush

@section('content')
@php
    $actions = [
        ['label' => 'Tambah Aturan', 'url' => route('admin.location.rules.create'), 'class' => 'btn btn-primary btn-sm'],
        ['label' => 'Daftar Booking', 'url' => panel_route('admin.bookings.list'), 'class' => 'btn btn-outline-primary btn-sm'],
    ];
@endphp

@include('pages.admin.partials.page-header', [
    'heading' => 'Aturan Harga Lokasi',
    'summary' => 'Kelola mapping lokasi ke tipe harga tambahan. Anda bisa set aturan level provinsi, kota/kabupaten, kecamatan, hingga kelurahan secara spesifik.',
    'actions' => $actions,
])

@if (session('status'))
    <div class="alert alert-success mb-3" role="alert">{{ session('status') }}</div>
@endif

@if ($errors->has('general'))
    <div class="alert alert-danger mb-3" role="alert">{{ $errors->first('general') }}</div>
@endif

@include('pages.admin.partials.alerts', [
    'alerts' => [
        ['class' => 'alert-info', 'text' => 'Contoh penggunaan: Provinsi Jawa Barat = Tambahan Sedang, kota Depok = Tambahan Ringan, dan kelurahan spesifik untuk aturan yang lebih detail.'],
    ],
])

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card custom-card mb-0 h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Total Aturan</p>
                <h4 class="mb-1 text-primary">{{ (int) ($stats['total'] ?? 0) }}</h4>
                <small class="text-muted d-block">Aturan harga lokasi aktif</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card custom-card mb-0 h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Level Provinsi</p>
                <h4 class="mb-1 text-success">{{ (int) ($stats['province'] ?? 0) }}</h4>
                <small class="text-muted d-block">Aturan untuk cakupan provinsi</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card custom-card mb-0 h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Level Kota/Kabupaten</p>
                <h4 class="mb-1 text-info">{{ (int) ($stats['city'] ?? 0) }}</h4>
                <small class="text-muted d-block">Aturan spesifik per kota/kabupaten</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card custom-card mb-0 h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Level Kecamatan</p>
                <h4 class="mb-1 text-warning">{{ (int) ($stats['district'] ?? 0) }}</h4>
                <small class="text-muted d-block">Aturan spesifik per kecamatan</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card custom-card mb-0 h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Level Kelurahan</p>
                <h4 class="mb-1 text-secondary">{{ (int) ($stats['village'] ?? 0) }}</h4>
                <small class="text-muted d-block">Aturan spesifik per kelurahan</small>
            </div>
        </div>
    </div>
</div>

<div class="card custom-card mb-0">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="card-title mb-0">Daftar Aturan Harga Lokasi</h5>
        <form method="GET" action="{{ route('admin.location.rules') }}" class="lpr-search-form">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fe fe-search"></i></span>
                <input
                    type="search"
                    class="form-control"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari lokasi atau tipe harga">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle mb-0">
                <thead>
                    <tr>
                        <th>Lokasi</th>
                        <th>Level</th>
                        <th>Lokasi Induk</th>
                        <th>Tipe Harga</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($rules ?? collect()) as $rule)
                        @php
                            $location = $rule->location;
                            $levelCode = strtoupper((string) ($location?->level?->code ?? ''));
                            $levelLabel = trim((string) ($location?->level?->description ?? 'Lokasi'));
                            $parentChain = [];
                            $parentLocation = $location?->parent;

                            while ($parentLocation) {
                                $parentChain[] = $parentLocation->name;
                                $parentLocation = $parentLocation->parent;
                            }

                            $parentLabel = $parentChain === []
                                ? '-'
                                : implode(' > ', array_reverse($parentChain));
                            $priceTypeCode = strtoupper((string) ($rule->priceType?->code ?? ''));
                            $priceTypeLabel = trim((string) ($rule->priceType?->description ?? '-'));
                            $priceBadgeClass = match ($priceTypeCode) {
                                'PT_RG' => 'bg-success-transparent text-success',
                                'PT_SD' => 'bg-warning-transparent text-warning',
                                'PT_CS' => 'bg-danger-transparent text-danger',
                                default => 'bg-secondary-transparent text-secondary',
                            };
                            $levelBadgeClass = match ($levelCode) {
                                'LL_PV' => 'bg-success-transparent text-success',
                                'LL_CT' => 'bg-info-transparent text-info',
                                'LL_KC' => 'bg-warning-transparent text-warning',
                                'LL_KL' => 'bg-secondary-transparent text-secondary',
                                default => 'bg-secondary-transparent text-secondary',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $location?->name ?? '-' }}</span>
                                    <small class="text-muted">{{ $rule->uuid }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $levelBadgeClass }}">
                                    {{ $levelLabel }}
                                </span>
                            </td>
                            <td>{{ $parentLabel }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $priceBadgeClass }}">{{ $priceTypeLabel }}</span>
                            </td>
                            <td>{{ $rule->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="text-end">
                                <div class="btn-list justify-content-end">
                                    <a href="{{ route('admin.location.rules.edit', $rule) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="{{ route('api.admin.location-rules.destroy', $rule) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus aturan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada aturan harga lokasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
