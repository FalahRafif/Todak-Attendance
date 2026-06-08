@php
    $isEdit = isset($managedPackage) && $managedPackage instanceof \App\Models\Package;
    $selectedStatusId = (string) old('status_id', $isEdit ? (string) $managedPackage->status_id : '');
    $selectedPackageTypeId = (string) old('package_type', $isEdit ? (string) $managedPackage->package_type : '');
    $existingBenefits = $isEdit ? $managedPackage->benefits->pluck('name')->toArray() : [];
    $oldBenefits = old('benefits', $existingBenefits);
    $oldName = old('name', $isEdit ? $managedPackage->name : '');
    $oldDescription = old('description', $isEdit ? $managedPackage->description : '');
    $oldPrice = old('price', $isEdit ? $managedPackage->price : '');
    $formattedPrice = $oldPrice !== '' ? number_format((float) $oldPrice, 0, ',', '.') : '';
@endphp

@if ($errors->has('general'))
    <div class="alert alert-danger mb-3" role="alert">{{ $errors->first('general') }}</div>
@endif

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="mp-form-card">
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
                    <div class="mp-thumbnail-upload-wrap text-center">
                        @php
                            $hasThumbnail = $isEdit && $managedPackage->thumbnailAttachment;
                            $thumbnailUrl = '';
                            if ($hasThumbnail) {
                                $thumbnailUrl = \Illuminate\Support\Facades\URL::signedRoute(
                                    'api.internal.attachments.preview',
                                    ['attachmentUuid' => $managedPackage->thumbnailAttachment->uuid],
                                    now()->addMinutes((int) config('app.attachments.temp_url_ttl_minutes', 30))
                                );
                            }
                        @endphp
                        <div id="thumbnail-preview-wrap" class="mb-3 @if(!$hasThumbnail) d-none @endif">
                            <img
                                id="thumbnail-preview"
                                src="{{ $thumbnailUrl }}"
                                alt="Thumbnail"
                                class="mp-thumbnail-preview img-fluid rounded"
                                style="max-height: 200px; object-fit: cover;">
                        </div>
                        <div id="thumbnail-placeholder" class="mp-thumbnail-placeholder @if($hasThumbnail) d-none @endif">
                            <div class="border border-dashed rounded p-4 text-muted">
                                <i class="fe fe-image fe-2x"></i>
                                <p class="mb-0 mt-2">Belum ada thumbnail</p>
                            </div>
                        </div>
                        <label for="thumbnail" class="form-label mt-3">Thumbnail (opsional)</label>
                        <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept=".jpg,.jpeg,.png,.webp">
                        @error('thumbnail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($hasThumbnail)
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="remove_thumbnail" name="remove_thumbnail" value="1">
                                <label class="form-check-label text-danger small" for="remove_thumbnail">Hapus thumbnail saat ini</label>
                            </div>
                        @endif
                        <small class="text-muted d-block mt-2">Format: JPG, PNG, WEBP. Maksimal 3MB.</small>
                    </div>
                </div>
                <div class="col-12 col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nama Paket <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ $oldName }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="price_display" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('price') is-invalid @enderror"
                                id="price_display"
                                value="{{ $formattedPrice }}"
                                placeholder="0"
                                autocomplete="off"
                                required>
                            <input type="hidden" id="price" name="price" value="{{ $oldPrice }}">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">Format otomatis: Rp 1.500.000</small>
                        </div>
                        <div class="col-md-6">
                            <label for="package_type" class="form-label">Tipe Paket <span class="text-danger">*</span></label>
                            <select id="package_type" name="package_type" class="form-select @error('package_type') is-invalid @enderror" required>
                                <option value="">Pilih tipe paket</option>
                                @foreach(($packageTypeOptions ?? collect()) as $pt)
                                    <option value="{{ $pt->id }}" @selected($selectedPackageTypeId === (string) $pt->id)>
                                        {{ $pt->description }} ({{ $pt->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('package_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status_id" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="status_id" name="status_id" class="form-select @error('status_id') is-invalid @enderror" required>
                                <option value="">Pilih status</option>
                                @foreach(($statusOptions ?? collect()) as $status)
                                    <option value="{{ $status->id }}" @selected($selectedStatusId === (string) $status->id)>
                                        {{ $status->description }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea
                                class="form-control @error('description') is-invalid @enderror"
                                id="description"
                                name="description"
                                rows="3"
                                maxlength="5000">{{ $oldDescription }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Benefit Paket</label>
                    <div id="benefits-container">
                        @foreach($oldBenefits as $index => $benefit)
                            <div class="input-group mb-2 mp-benefit-row">
                                <span class="input-group-text"><i class="fe fe-check-circle"></i></span>
                                <input
                                    type="text"
                                    class="form-control @error('benefits.' . $index) is-invalid @enderror"
                                    name="benefits[]"
                                    value="{{ $benefit }}"
                                    maxlength="500"
                                    placeholder="Contoh: Bisa melakukan editing foto tanpa batas">
                                <button type="button" class="btn btn-outline-danger mp-remove-benefit" title="Hapus benefit">
                                    <i class="fe fe-x"></i>
                                </button>
                                @error('benefits.' . $index)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-benefit-btn" class="btn btn-outline-primary btn-sm mt-1">
                        <i class="fe fe-plus me-1"></i> Tambah Benefit
                    </button>
                    @error('benefits')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-1">Tambahkan benefit yang didapatkan customer pada paket ini.</small>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex gap-2">
            <a href="{{ route('admin.packages') }}" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        </div>
    </div>
</form>
