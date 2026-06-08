@php
    $isEdit = isset($managedRule) && $managedRule instanceof \App\Models\Setting;
    $oldCode = old('code', $isEdit ? $managedRule->code : '');
    $oldDescription = old('description', $isEdit ? $managedRule->description : '');
    $selectedTypeId = (string) old('type_id', $isEdit ? (string) $managedRule->type_id : '');
    $oldValue = old('value', $isEdit ? $managedRule->value : '');
@endphp

@if ($errors->has('general'))
    <div class="alert alert-danger mb-3" role="alert">{{ $errors->first('general') }}</div>
@endif

<form method="POST" action="{{ $formAction }}" class="dpr-form-card">
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
                <div class="col-md-4">
                    <label for="code" class="form-label">Kode Aturan <span class="text-danger">*</span></label>
                    @if ($isEdit)
                        <input type="text" class="form-control" id="code" value="{{ $oldCode }}" disabled>
                        <input type="hidden" name="code" value="{{ $oldCode }}">
                        <small class="text-muted d-block mt-1">Kode tidak dapat diubah setelah dibuat.</small>
                    @else
                        <input
                            type="text"
                            class="form-control @error('code') is-invalid @enderror"
                            id="code"
                            name="code"
                            value="{{ $oldCode }}"
                            placeholder="Contoh: PTPP_WED"
                            required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Kode unik dalam grup persentase DP.</small>
                    @endif
                </div>
                <div class="col-md-4">
                    <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        class="form-control @error('description') is-invalid @enderror"
                        id="description"
                        name="description"
                        value="{{ $oldDescription }}"
                        placeholder="Contoh: Percentage DP Wedding"
                        required>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="type_id" class="form-label">Tipe Paket <span class="text-danger">*</span></label>
                    <select id="type_id" name="type_id" class="form-select @error('type_id') is-invalid @enderror" required>
                        <option value="">Pilih tipe paket</option>
                        @foreach(($packageTypeOptions ?? collect()) as $pt)
                            <option value="{{ $pt->id }}" @selected($selectedTypeId === (string) $pt->id)>
                                {{ $pt->description }}
                            </option>
                        @endforeach
                    </select>
                    @error('type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-1">Menentukan persentase DP untuk tipe paket ini.</small>
                </div>
                <div class="col-md-4">
                    <label for="value" class="form-label">Persentase DP (%) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input
                            type="number"
                            class="form-control @error('value') is-invalid @enderror"
                            id="value"
                            name="value"
                            value="{{ $oldValue }}"
                            min="0"
                            max="100"
                            step="1"
                            placeholder="Contoh: 15"
                            required>
                        <span class="input-group-text">%</span>
                    </div>
                    @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-1">Masukkan angka saja. Contoh: 15 untuk 15%.</small>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex gap-2">
            <a href="{{ route('admin.dp-percentage-rules') }}" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        </div>
    </div>
</form>
