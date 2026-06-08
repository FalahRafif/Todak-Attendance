@php
  $packageTypeOptions = $packageTypeOptions ?? collect();
  $packageOptions = $packageOptions ?? collect();
  $eventSessionOptions = $eventSessionOptions ?? collect();
  $provinceOptions = $provinceOptions ?? collect();

  $selectedPackageTypeId = (string) old('booking_package_type', '');
  $selectedProvinceId = (string) old('booking_location_province', '');
  $selectedCityId = (string) old('booking_location_city', '');
  $selectedDistrictId = (string) old('booking_location_district', '');
  $selectedVillageId = (string) old('booking_location_village', '');
@endphp

<div class="booking-grid">
  <aside class="booking-panel booking-availability" aria-labelledby="availability_title">
    <h3 id="availability_title">Cek Ketersediaan Tanggal</h3>
    <p class="booking-caption">Maksimal 2 booking per hari, terbagi sesi pagi-siang dan sore-malam. Slot baru terblokir setelah DP diverifikasi.</p>

    <label class="form-label" for="booking_date_check">Tanggal Acara</label>
    <input
      class="form-input"
      type="date"
      id="booking_date_check"
      name="booking_date"
      form="booking_form_preview"
      value="{{ old('booking_date') }}"
      data-availability-url="{{ route('booking.availability', [], false) }}"
      required>
    @error('booking_date')
      <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
    @enderror

    <p class="availability-summary" id="availability_summary">Pilih tanggal untuk melihat status slot.</p>

    <div class="slot-list">
      <article class="slot-card" data-slot-card="morning">
        <p class="slot-name">Pagi-Siang</p>
        <p class="slot-status" data-slot-status="morning">Belum dipilih</p>
      </article>
      <article class="slot-card" data-slot-card="evening">
        <p class="slot-name">Sore-Malam</p>
        <p class="slot-status" data-slot-status="evening">Belum dipilih</p>
      </article>
    </div>

    <div class="form-field mt-3">
      <label class="form-label" for="booking_session">Sesi Acara</label>
      <select class="form-input form-select booking-select2" id="booking_session" name="booking_session" form="booking_form_preview" data-placeholder="Pilih sesi acara" required>
        <option value="">Pilih sesi acara</option>
        @foreach ($eventSessionOptions as $sessionOption)
          <option value="{{ $sessionOption->id }}" data-session-code="{{ strtoupper((string) $sessionOption->code) }}" @selected((string) old('booking_session') === (string) $sessionOption->id)>
            {{ $sessionOption->description }}
          </option>
        @endforeach
      </select>
      @error('booking_session')
        <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
      @enderror
    </div>

    <p class="booking-note">First come first serve mengikuti urutan DP terverifikasi.</p>

    <section class="booking-estimate-panel" id="booking_estimate_panel" data-estimate-url="{{ route('booking.estimate', [], false) }}">
      <h4 class="booking-estimate-title">Perkiraan Biaya Awal Booking</h4>
      <p class="booking-disclaimer mt-1 mb-2">Bagian ini membantu Anda melihat gambaran biaya awal berdasarkan paket yang dipilih, kategori lokasi acara, dan skema pembayaran.</p>

      <div class="booking-estimate-item">
        <p class="booking-estimate-label">Ringkasan Paket</p>
        <p class="booking-estimate-value" id="estimate_package_name">Pilih tipe paket dan paket untuk melihat perkiraan biaya.</p>
        <p class="booking-disclaimer mt-1 mb-0" id="estimate_package_type">-</p>
        <p class="booking-disclaimer mt-1 mb-0" id="estimate_package_price">Harga paket: -</p>
        <p class="booking-disclaimer mt-1 mb-0" id="estimate_package_address">Alamat paket: -</p>
        <ul class="booking-estimate-benefits" id="estimate_package_benefits"></ul>
      </div>

      <div class="booking-estimate-item">
        <p class="booking-estimate-label">Biaya Tambahan Lokasi</p>
        <p class="booking-estimate-value" id="estimate_location_rule">Lengkapi lokasi acara untuk melihat kategori tambahan biaya.</p>
        <p class="booking-disclaimer mt-1 mb-0">Biaya tambahan akan dikenakan berdasarkan kategori lokasi acara.</p>
      </div>

      <div class="booking-estimate-item">
        <p class="booking-estimate-label">Rencana Pembayaran</p>
        <p class="booking-estimate-value mb-1" id="estimate_dp_percentage">DP: -</p>
        <p class="booking-disclaimer mt-0 mb-0" id="estimate_dp_amount">Nominal DP: -</p>
        <p class="booking-disclaimer mt-1 mb-0" id="estimate_dp_note">Batas waktu DP: -</p>
        <p class="booking-disclaimer mt-1 mb-0" id="estimate_final_note">Batas pelunasan: -</p>
        <p class="booking-disclaimer mt-1 mb-0" id="estimate_final_due_date">Tanggal batas pelunasan: -</p>
      </div>

      <p class="booking-estimate-warning mb-0">Catatan penting: ini masih estimasi awal. Harga akhir bisa bertambah setelah tim meninjau detail lokasi, akses venue, kebutuhan teknis acara, transportasi, dan akomodasi.</p>
    </section>
  </aside>

  <form
    class="booking-panel booking-form"
    id="booking_form_preview"
    action="{{ route('booking.store', [], false) }}"
    method="post"
    data-location-options-url="{{ route('booking.location.options', [], false) }}"
    data-estimate-url="{{ route('booking.estimate', [], false) }}">
    @csrf

    <h3>Request Booking</h3>

    @if ($errors->has('general'))
      <div class="alert alert-danger mb-3" role="alert">{{ $errors->first('general') }}</div>
    @endif

    <div class="booking-form-grid">
      <div class="form-field">
        <label class="form-label" for="booking_name">Nama Lengkap</label>
        <input class="form-input" type="text" id="booking_name" name="booking_name" value="{{ old('booking_name') }}" placeholder="Contoh: Aulia Pratama" required>
        @error('booking_name')
          <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
        @enderror
      </div>

      <div class="form-field">
        <label class="form-label" for="booking_whatsapp">No WhatsApp</label>
        <input class="form-input" type="tel" id="booking_whatsapp" name="booking_whatsapp" value="{{ old('booking_whatsapp') }}" placeholder="08xxxxxxxxxx" required>
        @error('booking_whatsapp')
          <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
        @enderror
      </div>

      <div class="form-field">
        <label class="form-label" for="booking_package_type">Tipe Paket</label>
        <select class="form-input form-select booking-select2" id="booking_package_type" name="booking_package_type" data-placeholder="Pilih tipe paket" required>
          <option value="">Pilih tipe paket</option>
          @foreach ($packageTypeOptions as $packageTypeOption)
            <option value="{{ $packageTypeOption->id }}" @selected($selectedPackageTypeId === (string) $packageTypeOption->id)>
              {{ $packageTypeOption->description }}
            </option>
          @endforeach
        </select>
        @error('booking_package_type')
          <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
        @enderror
      </div>

      <div class="form-field">
        <label class="form-label" for="booking_package">Paket</label>
        <select class="form-input form-select booking-select2" id="booking_package" name="booking_package" data-placeholder="Pilih paket" required>
          <option value="">Pilih paket</option>
          @foreach ($packageOptions as $packageOption)
            @php
              $packageTypeLabel = trim((string) ($packageOption->packageType?->description ?? 'PACKAGE'));
            @endphp
            <option
              value="{{ $packageOption->id }}"
              data-package-type="{{ (string) $packageOption->package_type }}"
              data-package-address="{{ trim((string) ($packageOption->address ?? '')) }}"
              @selected((string) old('booking_package') === (string) $packageOption->id)>
              {{ $packageOption->name }} - Rp {{ number_format((float) $packageOption->price, 0, ',', '.') }} ({{ $packageTypeLabel }})
            </option>
          @endforeach
        </select>
        <p class="booking-disclaimer mt-2 mb-0" id="booking_package_address_preview" data-default-text="Pilih paket untuk melihat referensi alamat paket.">Pilih paket untuk melihat referensi alamat paket.</p>
        @error('booking_package')
          <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
        @enderror
      </div>
      <div class="form-field form-field-full">
        <label class="form-label" for="booking_detail">Detail Acara</label>
        <textarea class="form-input form-textarea" id="booking_detail" name="booking_detail" rows="4" placeholder="Tuliskan rundown singkat, kebutuhan khusus, dan catatan penting lainnya.">{{ old('booking_detail') }}</textarea>
        @error('booking_detail')
          <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
        @enderror
      </div>

      <div class="form-field form-field-full booking-location-fieldset">
        <label class="form-label" for="booking_location_province">Lokasi Acara</label>
        <p class="booking-disclaimer mb-2">Pilih lokasi berurutan mulai dari provinsi sampai kelurahan agar tim bisa menghitung biaya tambahan lokasi secara akurat.</p>

        <input type="hidden" id="booking_location" name="booking_location" value="{{ old('booking_location', $selectedVillageId) }}">

        <div class="booking-location-grid">
          <div class="booking-location-item">
            <label class="form-label" for="booking_location_province">Provinsi</label>
            <select class="form-input form-select booking-select2" id="booking_location_province" name="booking_location_province" data-placeholder="Pilih provinsi" required>
              <option value="">Pilih provinsi</option>
              @foreach ($provinceOptions as $provinceOption)
                <option value="{{ $provinceOption->id }}" @selected($selectedProvinceId === (string) $provinceOption->id)>
                  {{ $provinceOption->name }}
                </option>
              @endforeach
            </select>
            @error('booking_location_province')
              <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
            @enderror
          </div>

          <div class="booking-location-item">
            <label class="form-label" for="booking_location_city">Kota/Kabupaten</label>
            <select
              class="form-input form-select booking-select2"
              id="booking_location_city"
              name="booking_location_city"
              data-placeholder="Pilih kota/kabupaten"
              data-selected="{{ $selectedCityId }}"
              disabled
              required>
              <option value="">Pilih kota/kabupaten</option>
            </select>
            @error('booking_location_city')
              <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
            @enderror
          </div>

          <div class="booking-location-item">
            <label class="form-label" for="booking_location_district">Kecamatan</label>
            <select
              class="form-input form-select booking-select2"
              id="booking_location_district"
              name="booking_location_district"
              data-placeholder="Pilih kecamatan"
              data-selected="{{ $selectedDistrictId }}"
              disabled
              required>
              <option value="">Pilih kecamatan</option>
            </select>
            @error('booking_location_district')
              <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
            @enderror
          </div>

          <div class="booking-location-item">
            <label class="form-label" for="booking_location_village">Kelurahan</label>
            <select
              class="form-input form-select booking-select2"
              id="booking_location_village"
              name="booking_location_village"
              data-placeholder="Pilih kelurahan"
              data-selected="{{ $selectedVillageId }}"
              disabled
              required>
              <option value="">Pilih kelurahan</option>
            </select>
            @error('booking_location_village')
              <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
            @enderror
          </div>
        </div>

        @error('booking_location')
          <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
        @enderror

        <div class="booking-pin-item booking-pin-item-full">
            <label class="form-label" for="booking_pin_address">Detail Alamat & Patokan</label>
            <textarea class="form-input form-textarea" id="booking_pin_address" name="booking_pin_address" rows="3" placeholder="Contoh: Gedung Serbaguna X, dekat gerbang utara, lantai 2." required>{{ old('booking_pin_address') }}</textarea>
            @error('booking_pin_address')
              <p class="booking-disclaimer text-danger mb-0">{{ $message }}</p>
            @enderror
          </div>
      </div>
      
      <div class="form-field form-field-full booking-pin-fieldset">
        <label class="form-label" for="booking_pin_address">Pin Lokasi Acara</label>
        <p class="booking-disclaimer mb-2">Masukkan titik koordinat lokasi seperti aplikasi transportasi online. Anda bisa copy dari Google Maps dengan menekan lama titik lokasi.</p>

        <div class="booking-map-picker-wrap">
          <div id="booking_map_picker" class="booking-map-picker" aria-label="Peta pemilihan pin lokasi acara"></div>
          <p class="booking-disclaimer mt-2">Klik pada peta untuk meletakkan pin, lalu sesuaikan titik dengan drag marker agar lebih presisi.</p>
        </div>

        <div class="booking-pin-grid">
          <input type="hidden" id="booking_pin_lat" name="booking_pin_lat" value="{{ old('booking_pin_lat') }}">
          <input type="hidden" id="booking_pin_lng" name="booking_pin_lng" value="{{ old('booking_pin_lng') }}">
          <p class="booking-disclaimer mt-0 mb-0 booking-pin-coordinate-hint" id="booking_pin_coordinate_hint">Koordinat belum dipilih. Silakan klik pin pada peta.</p>
          @if($errors->has('booking_pin_lat') || $errors->has('booking_pin_lng'))
            <p class="booking-disclaimer text-danger mb-0">Silakan pilih titik pin di peta terlebih dahulu.</p>
          @endif

        </div>
      </div>
    </div>

    <div class="booking-actions">
      <button class="cta booking-submit" id="booking_submit_button" type="submit">Kirim Request Booking</button>
      <p class="booking-disclaimer">Data request akan masuk ke tim admin untuk proses review sebelum tahap pembayaran DP.</p>
      <p class="booking-disclaimer"><a href="{{ route('booking.status') }}">Sudah pernah booking? Cek status di sini.</a></p>
    </div>
  </form>

  <div class="booking-confirm-modal" id="booking_confirmation_modal" hidden>
    <div class="booking-confirm-backdrop" data-booking-confirm-close></div>
    <div class="booking-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="booking_confirm_title">
      <div class="booking-confirm-header">
        <div class="booking-confirm-header-content">
          <div class="booking-confirm-badge">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
          </div>
          <div>
            <h4 id="booking_confirm_title">Konfirmasi Data Booking</h4>
            <p class="booking-confirm-subtitle">Mohon periksa kembali data yang Anda masukkan</p>
          </div>
        </div>
        <button class="booking-confirm-close" type="button" aria-label="Tutup konfirmasi" data-booking-confirm-close>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <p class="booking-confirm-intro">Pastikan semua data di bawah ini sudah benar dan sesuai sebelum mengirim request booking.</p>

      <div class="booking-confirm-list">
        <div class="booking-confirm-row booking-confirm-row-highlight">
          <span class="booking-confirm-key">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="booking-confirm-icon">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Nama
          </span>
          <span class="booking-confirm-value" id="confirm_name">-</span>
        </div>
        <div class="booking-confirm-row">
          <span class="booking-confirm-key">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="booking-confirm-icon">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
            No WhatsApp
          </span>
          <span class="booking-confirm-value" id="confirm_whatsapp">-</span>
        </div>
        <div class="booking-confirm-row booking-confirm-row-highlight">
          <span class="booking-confirm-key">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="booking-confirm-icon">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            Tanggal & Sesi
          </span>
          <span class="booking-confirm-value" id="confirm_schedule">-</span>
        </div>
        <div class="booking-confirm-row">
          <span class="booking-confirm-key">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="booking-confirm-icon">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
            </svg>
            Tipe & Paket
          </span>
          <span class="booking-confirm-value" id="confirm_package">-</span>
        </div>
        <div class="booking-confirm-row">
          <span class="booking-confirm-key">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="booking-confirm-icon">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
            Lokasi Acara
          </span>
          <span class="booking-confirm-value" id="confirm_location">-</span>
        </div>
        <div class="booking-confirm-row">
          <span class="booking-confirm-key">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="booking-confirm-icon">
              <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
            </svg>
            Pin Lokasi
          </span>
          <span class="booking-confirm-value" id="confirm_pin">-</span>
        </div>
        <div class="booking-confirm-row">
          <span class="booking-confirm-key">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="booking-confirm-icon">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Detail Alamat
          </span>
          <span class="booking-confirm-value" id="confirm_address_detail">-</span>
        </div>
        <div class="booking-confirm-row">
          <span class="booking-confirm-key">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="booking-confirm-icon">
              <line x1="8" y1="6" x2="21" y2="6"></line>
              <line x1="8" y1="12" x2="21" y2="12"></line>
            <line x1="8" y1="18" x2="21" y2="18"></line>
            <line x1="3" y1="6" x2="3.01" y2="6"></line>
            <line x1="3" y1="12" x2="3.01" y2="12"></line>
            <line x1="3" y1="18" x2="3.01" y2="18"></line>
          </svg>
          Detail Acara
        </span>
        <span class="booking-confirm-value" id="confirm_event_detail">-</span>
      </div>
    </div>

    <label class="booking-confirm-checkbox" for="booking_confirm_checkbox">
      <input type="checkbox" id="booking_confirm_checkbox">
      <span class="booking-confirm-checkbox-text">
        <span class="booking-confirm-checkbox-main">Saya sudah melakukan pengecekan dan data yang saya isi sudah benar.</span>
        <span class="booking-confirm-checkbox-note">Data yang sudah dikirim tidak dapat diubah</span>
      </span>
    </label>

    <div class="booking-confirm-actions">
      <button class="cta cta-outline booking-confirm-cancel" type="button" data-booking-confirm-close>Periksa Ulang</button>
      <button class="cta booking-confirm-submit" id="booking_confirm_submit" type="button" disabled>
        <span class="booking-confirm-submit-text">Kirim Request Sekarang</span>
      </button>
    </div>
  </div>
</div>
</div>
