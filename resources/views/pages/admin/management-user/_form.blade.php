@php
    $isEdit = isset($managedUser) && $managedUser instanceof \App\Models\User;
    $profileUrl = $profileImageUrl ?? null;
    if (!is_string($profileUrl) || trim($profileUrl) === '') {
        $profileUrl = asset('assets/images/faces/2.jpg');
    }
@endphp

@if ($errors->has('general'))
    <div class="alert alert-danger mb-3" role="alert">{{ $errors->first('general') }}</div>
@endif

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="um-form-card">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="card custom-card mb-0">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ $formTitle }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-lg-4">
                    <div class="um-profile-upload-wrap">
                        <img
                            src="{{ $profileUrl }}"
                            alt="Profile Image Preview"
                            id="profile-image-preview"
                            class="um-profile-preview"
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
                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name', $isEdit ? $managedUser->name : '') }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input
                                type="text"
                                class="form-control @error('username') is-invalid @enderror"
                                id="username"
                                name="username"
                                value="{{ old('username', $isEdit ? ($managedUser->username ?? '') : '') }}"
                                maxlength="100">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email', $isEdit ? $managedUser->email : '') }}"
                                required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                            <select id="role_id" name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">Pilih role</option>
                                @foreach(($roles ?? collect()) as $role)
                                    @php
                                        $selectedRoleId = old('role_id', $isEdit ? (string) $managedUser->role_id : '');
                                    @endphp
                                    <option value="{{ $role->id }}" @selected((string) $selectedRoleId === (string) $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="password" class="form-label">
                        Password
                        @if(!$isEdit)
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <input
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        @if(!$isEdit) required @endif
                        minlength="8">
                    <small class="text-muted d-block mt-1">
                        @if($isEdit)
                            Kosongkan jika tidak ingin mengubah password.
                        @else
                            Minimal 8 karakter.
                        @endif
                    </small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">
                        Konfirmasi Password
                        @if(!$isEdit)
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <input
                        type="password"
                        class="form-control @error('password_confirmation') is-invalid @enderror"
                        id="password_confirmation"
                        name="password_confirmation"
                        @if(!$isEdit) required @endif
                        minlength="8">
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <a href="{{ route('admin.users') }}" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        </div>
    </div>
</form>
