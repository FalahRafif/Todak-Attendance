@extends('layouts.admin.admin')

@section('title', $title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/pages/admin/management-package/index.css') }}">
@endpush

@section('content')
@php
    $actions = [
        ['label' => 'Tambah Paket', 'url' => route('admin.packages.create'), 'class' => 'btn btn-primary btn-sm'],
    ];
@endphp

@include('pages.admin.partials.page-header', [
    'heading' => 'Management Paket',
    'summary' => 'Kelola paket layanan dokumentasi foto/video untuk kebutuhan wedding dan non-wedding.',
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
        ['class' => 'alert-info', 'text' => 'Paket dengan status DRAFT tidak akan tampil di halaman publik. Pastikan ubah status ke ACTIVE setelah data lengkap.'],
    ],
])

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card custom-card mb-0 h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Total Paket</p>
                <h4 class="mb-1 text-primary">{{ (int) ($stats['total'] ?? 0) }}</h4>
                <small class="text-muted d-block">Seluruh paket aktif</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card custom-card mb-0 h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Active</p>
                <h4 class="mb-1 text-success">{{ (int) ($stats['active'] ?? 0) }}</h4>
                <small class="text-muted d-block">Paket aktif dan visible</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card custom-card mb-0 h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Draft</p>
                <h4 class="mb-1 text-warning">{{ (int) ($stats['draft'] ?? 0) }}</h4>
                <small class="text-muted d-block">Belum dipublikasikan</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card custom-card mb-0 h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Inactive</p>
                <h4 class="mb-1 text-secondary">{{ (int) ($stats['inactive'] ?? 0) }}</h4>
                <small class="text-muted d-block">Paket dinonaktifkan</small>
            </div>
        </div>
    </div>
</div>

<div class="card custom-card mb-0">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="card-title mb-0">Daftar Paket</h5>
        <form method="GET" action="{{ route('admin.packages') }}" class="mp-search-form">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fe fe-search"></i></span>
                <input
                    type="search"
                    class="form-control"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama, deskripsi, status, atau tipe">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle mb-0">
                <thead>
                    <tr>
                        <th>Paket</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Harga</th>
                        <th>Benefit</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($packages ?? collect()) as $package)
                        @php
                            $statusCode = strtoupper((string) ($package->status?->code ?? ''));
                            $statusLabel = trim((string) ($package->status?->description ?? '-'));
                            $statusBadge = match ($statusCode) {
                                'PS_ACTIVE' => 'bg-success-transparent text-success',
                                'PS_DRAFT' => 'bg-warning-transparent text-warning',
                                'PS_INACTIVE' => 'bg-secondary-transparent text-secondary',
                                default => 'bg-secondary-transparent text-secondary',
                            };
                            $typeCode = strtoupper((string) ($package->packageType?->code ?? ''));
                            $typeLabel = trim((string) ($package->packageType?->description ?? '-'));
                            $typeBadge = match ($typeCode) {
                                'PKT_WEDDING' => 'bg-pink-transparent text-pink',
                                'PKT_NON_WEDDING' => 'bg-info-transparent text-info',
                                default => 'bg-secondary-transparent text-secondary',
                            };
                            $benefitCount = $package->benefits->count();
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $package->name }}</span>
                                    <small class="text-muted text-truncate" style="max-width: 200px;">{{ Str::limit($package->description ?? '-', 50) }}</small>
                                </div>
                            </td>
                            <td><span class="badge rounded-pill {{ $typeBadge }}">{{ $typeLabel }}</span></td>
                            <td><span class="badge rounded-pill {{ $statusBadge }}">{{ $statusLabel }}</span></td>
                            <td>Rp {{ number_format((float) $package->price, 0, ',', '.') }}</td>
                            <td>
                                @if($benefitCount > 0)
                                    <span class="badge bg-primary-transparent text-primary">{{ $benefitCount }} benefit</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $package->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="text-end">
                                <div class="btn-list justify-content-end">
                                    <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="{{ route('api.admin.packages.destroy', $package) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus paket ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data paket.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
