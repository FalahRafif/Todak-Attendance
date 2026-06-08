@extends('layouts.admin.admin')

@section('title', $title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/pages/admin/management-user/index.css') }}">
@endpush

@section('content')
@php
    $actions = [
        ['label' => 'Tambah Akun', 'url' => route('admin.users.create'), 'class' => 'btn btn-primary btn-sm'],
        ['label' => 'Settings', 'url' => panel_route('admin.settings'), 'class' => 'btn btn-outline-primary btn-sm'],
    ];
@endphp

@include('pages.admin.partials.page-header', [
    'heading' => 'Management User/Akun',
    'summary' => 'Kelola akun internal untuk role Admin dan Petugas melalui halaman terpisah agar maintainable dan jelas per scope.',
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
        ['class' => 'alert-info', 'text' => 'Hanya Admin yang dapat mengelola akun internal. Role input dibatasi pada Admin dan Petugas.'],
    ],
])

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card custom-card mb-0 h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Total Akun Internal</p>
                <h4 class="mb-1 text-primary">{{ (int) ($stats['total'] ?? 0) }}</h4>
                <small class="text-muted d-block">Akun aktif pada panel internal</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card custom-card mb-0 h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Role Admin</p>
                <h4 class="mb-1 text-success">{{ (int) ($stats['admin'] ?? 0) }}</h4>
                <small class="text-muted d-block">Akses penuh panel</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card custom-card mb-0 h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Role Petugas</p>
                <h4 class="mb-1 text-info">{{ (int) ($stats['petugas'] ?? 0) }}</h4>
                <small class="text-muted d-block">Akses operasional</small>
            </div>
        </div>
    </div>
</div>

<div class="card custom-card mb-0">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="card-title mb-0">Daftar Akun Internal</h5>
        <form method="GET" action="{{ route('admin.users') }}" class="um-search-form">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fe fe-search"></i></span>
                <input
                    type="search"
                    class="form-control"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama, username, atau email">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($users ?? collect()) as $user)
                        @php
                            $roleName = $user->roleName();
                            $badgeClass = $roleName === 'Admin'
                                ? 'bg-success-transparent text-success'
                                : 'bg-info-transparent text-info';
                            $profileUrl = trim((string) ($user->profile_image_url ?? ''));
                            if ($profileUrl === '') {
                                $profileUrl = asset('assets/images/faces/2.jpg');
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img
                                        src="{{ $profileUrl }}"
                                        alt="{{ $user->name }}"
                                        class="um-avatar"
                                        onerror="this.onerror=null;this.src='{{ asset('assets/images/faces/2.jpg') }}';">
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $user->name }}</span>
                                        <small class="text-muted">{{ $user->uuid }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->username ?: '-' }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge rounded-pill {{ $badgeClass }}">{{ $roleName }}</span></td>
                            <td>{{ $user->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="text-end">
                                <div class="btn-list justify-content-end">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="{{ route('api.admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus akun ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data user internal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
