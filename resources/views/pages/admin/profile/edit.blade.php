@extends('layouts.admin.admin')

@section('title', $title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/pages/admin/profile/edit.css') }}">
@endpush

@section('content')
@include('pages.admin.partials.page-header', [
    'heading' => 'Profil Saya',
    'summary' => 'Perbarui informasi akun Anda. Perubahan yang berhasil akan langsung sinkron ke session aktif.',
    'actions' => [
        ['label' => 'Kembali ke Dashboard', 'url' => panel_route('admin.dashboard'), 'class' => 'btn btn-outline-primary btn-sm'],
    ],
])

@if (session('status'))
    <div class="alert alert-success mb-3" role="alert">{{ session('status') }}</div>
@endif

@if ($errors->has('general'))
    <div class="alert alert-danger mb-3" role="alert">{{ $errors->first('general') }}</div>
@endif

<form method="POST" action="{{ route('api.admin.profile.update') }}" enctype="multipart/form-data" class="profile-form-wrap">
    @csrf
    @method('PUT')

    <div class="card custom-card mb-0">
        <div class="card-header">
            <h5 class="card-title mb-0">Update Profil</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-lg-4">
                    <div class="profile-image-wrap">
                        <img
                            src="{{ $profileImageUrl ?: asset('assets/images/faces/2.jpg') }}"
                            alt="Profile Image"
                            id="profile-image-preview"
                            class="profile-image-preview"
                            onerror="this.onerror=null;this.src='{{ asset('assets/images/faces/2.jpg') }}';">
                        <label for="profile_image" class="form-label mt-3">Foto Profile (opsional)</label>
                        <input type="file" class="form-control @error('profile_image') is-invalid @enderror" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png,.webp">
                        @error('profile_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-2">Format: JPG, PNG, WEBP. Maksimal 3MB.</small>
                    </div>
                </div>
                <div class="col-12 col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $profileUser->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $profileUser->username) }}">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $profileUser->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="{{ $profileUser->roleName() }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="password" class="form-label">Password Baru</label>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8">
                    <small class="text-muted d-block mt-1">Kosongkan jika tidak ingin mengubah password.</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" minlength="8">
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Simpan Profil</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
    <script src="{{ asset('assets/pages/admin/profile/edit.js') }}"></script>
@endpush
