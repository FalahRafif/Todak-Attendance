@php
    $isEdit = isset($managedRule) && $managedRule instanceof \App\Models\LocationPricingRule;
    $locationLevels = collect($locationLevels ?? []);
    $locationPath = $locationPath ?? [];
    $selectedLocationId = (string) old('location_id', $locationPath['location_id'] ?? ($isEdit ? (string) $managedRule->location_id : ''));
    $selectedLevelCode = (string) old('location_level', $locationPath['level_code'] ?? 'LL_PV');
    $selectedProvinceId = (string) old('location_province_id', $locationPath['province_id'] ?? '');
    $selectedCityId = (string) old('location_city_id', $locationPath['city_id'] ?? '');
    $selectedDistrictId = (string) old('location_district_id', $locationPath['district_id'] ?? '');
    $selectedVillageId = (string) old('location_village_id', $locationPath['village_id'] ?? '');
    $selectedPriceTypeId = (string) old('price_type', $isEdit ? (string) $managedRule->price_type : '');
@endphp

@if ($errors->has('general'))
    <div class="alert alert-danger mb-3" role="alert">{{ $errors->first('general') }}</div>
@endif

<form method="POST" action="{{ $formAction }}" class="lpr-form-card" data-location-options-url="{{ route('api.admin.location-rules.options') }}">
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
                <div class="col-12 col-lg-6">
                    <label for="location_level" class="form-label">Tipe Lokasi <span class="text-danger">*</span></label>
                    <select id="location_level" name="location_level" class="form-select select2" required>
                        <option value="">Pilih tipe lokasi</option>
                        @foreach($locationLevels as $level)
                            <option value="{{ $level['code'] }}" @selected($selectedLevelCode === $level['code'])>
                                {{ $level['label'] }}
                            </option>
                        @endforeach
                    </select>

                    <input type="hidden" id="location_id" name="location_id" value="{{ $selectedLocationId }}">

                    <div class="mt-3 d-none" data-location-wrapper="LL_PV">
                        <label for="location_province_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                        <select id="location_province_id" name="location_province_id" class="form-select select2" data-selected="{{ $selectedProvinceId }}" data-placeholder="Pilih provinsi" disabled>
                            <option value=""></option>
                        </select>
                    </div>

                    <div class="mt-3 d-none" data-location-wrapper="LL_CT">
                        <label for="location_city_id" class="form-label">Kota/Kabupaten <span class="text-danger">*</span></label>
                        <select id="location_city_id" name="location_city_id" class="form-select select2" data-selected="{{ $selectedCityId }}" data-placeholder="Pilih kota/kabupaten" disabled>
                            <option value=""></option>
                        </select>
                    </div>

                    <div class="mt-3 d-none" data-location-wrapper="LL_KC">
                        <label for="location_district_id" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                        <select id="location_district_id" name="location_district_id" class="form-select select2" data-selected="{{ $selectedDistrictId }}" data-placeholder="Pilih kecamatan" disabled>
                            <option value=""></option>
                        </select>
                    </div>

                    <div class="mt-3 d-none" data-location-wrapper="LL_KL">
                        <label for="location_village_id" class="form-label">Kelurahan <span class="text-danger">*</span></label>
                        <select id="location_village_id" name="location_village_id" class="form-select select2" data-selected="{{ $selectedVillageId }}" data-placeholder="Pilih kelurahan" disabled>
                            <option value=""></option>
                        </select>
                    </div>

                    @error('location_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-1">Pilih tipe lokasi, lalu tentukan cakupan lokasi sesuai level yang dipilih.</small>
                </div>

                <div class="col-12 col-lg-6">
                    <label for="price_type" class="form-label">Tipe Harga <span class="text-danger">*</span></label>
                    <select id="price_type" name="price_type" class="form-select select2 @error('price_type') is-invalid @enderror" required>
                        <option value="">Pilih tipe harga</option>
                        @foreach(($priceTypeOptions ?? collect()) as $priceType)
                            <option value="{{ $priceType->id }}" @selected($selectedPriceTypeId === (string) $priceType->id)>
                                {{ $priceType->description }} ({{ $priceType->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('price_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-1">Contoh: Tambahan Ringan, Tambahan Sedang, atau Tambahan Custom.</small>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex gap-2">
            <a href="{{ route('admin.location.rules') }}" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        </div>
    </div>
</form>
