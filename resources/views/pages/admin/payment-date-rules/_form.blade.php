@php
    $isEdit = isset($managedRule) && $managedRule instanceof \App\Models\Setting;
    $oldCode = old('code', $isEdit ? $managedRule->code : '');
    $oldDescription = old('description', $isEdit ? $managedRule->description : '');
    $oldValue = old('value', $isEdit ? $managedRule->value : '');
    $parsedType = 'H+';
    $parsedDays = '';
    if (preg_match('/^(H[+-])(\d+)$/', strtoupper(trim((string) $oldValue)), $m)) {
        $parsedType = $m[1];
        $parsedDays = $m[2];
    }
    $selectedType = old('value_type', $parsedType);
    $selectedDays = old('value_days', $parsedDays);
@endphp

@if ($errors->has('general'))
    <div class="alert alert-danger mb-3" role="alert">{{ $errors->first('general') }}</div>
@endif

<form method="POST" action="{{ $formAction }}" class="pdr-form-card">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <input type="hidden" id="value" name="value" value="{{ $oldValue }}">

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
                            placeholder="Contoh: PDR_DP"
                            required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Kode unik dalam grup aturan waktu pembayaran.</small>
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
                        placeholder="Contoh: DP Maksimal Hari"
                        required>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="value_type" class="form-label">Tipe Perhitungan <span class="text-danger">*</span></label>
                    <select id="value_type" name="value_type" class="form-select" required>
                        <option value="H+" {{ $selectedType === 'H+' ? 'selected' : '' }}>H+ (Setelah Approval)</option>
                        <option value="H-" {{ $selectedType === 'H-' ? 'selected' : '' }}>H- (Sebelum Acara)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="value_days" class="form-label">Jumlah Hari <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        class="form-control @error('value_days') is-invalid @enderror"
                        id="value_days"
                        name="value_days"
                        value="{{ $selectedDays }}"
                        min="1"
                        max="365"
                        placeholder="Contoh: 3"
                        required>
                    @error('value_days')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <div id="value-note"></div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex gap-2">
            <a href="{{ route('admin.payment-date-rules') }}" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    "use strict";

    function initValuePreview() {
        var $type = $("#value_type");
        var $days = $("#value_days");
        var $hidden = $("#value");
        var $note = $("#value-note");
        if (!$type.length || !$days.length || !$hidden.length || !$note.length) {
            return;
        }

        function update() {
            var type = $type.val() || "H+";
            var days = parseInt($days.val(), 10);

            if (isNaN(days) || days <= 0) {
                $hidden.val("");
                $note.empty();
                return;
            }

            $hidden.val(type + days);

            if (type === "H+") {
                $note.html(
                    '<div class="alert alert-info py-2 px-3 mb-0 small">' +
                        '<i class="fe fe-info me-1"></i> ' +
                        'Dihitung <strong>' + days + ' hari setelah approval/penawaran dibuat</strong>. Format: <code>H+' + days + '</code>' +
                    '</div>'
                );
            } else {
                $note.html(
                    '<div class="alert alert-warning py-2 px-3 mb-0 small">' +
                        '<i class="fe fe-alert-triangle me-1"></i> ' +
                        'Dihitung <strong>' + days + ' hari sebelum tanggal acara booking</strong>. Format: <code>H-' + days + '</code>' +
                    '</div>'
                );
            }
        }

        $type.on("change", update);
        $days.on("input", update);
        update();
    }

    $(function () {
        initValuePreview();
    });
})();
</script>
@endpush
