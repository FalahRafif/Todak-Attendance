@extends('layouts.guest.guest')

@section('content')
<section class="section-block container booking-section">
  <div class="section-heading booking-heading">
    <p class="eyebrow">Detail Booking</p>
    <h2>Detail booking Anda</h2>
    <p class="section-lead">Masukkan kode booking, lalu verifikasi dengan 4 digit terakhir nomor WhatsApp agar data booking tampil aman dan akurat.</p>
  </div>

  <div class="booking-grid booking-grid-single">
    <form class="booking-panel" id="booking_status_lookup_form" action="#" method="get" data-status-lookup-url="{{ route('booking.status.lookup', [], false) }}" data-upload-payment-url="{{ route('booking.upload-payment-proof', [], false) }}">
      <h3>Cari Data Booking</h3>
      <div class="booking-form-grid">
        <div class="form-field form-field-full">
          <label class="form-label" for="booking_code">Kode Booking / Case ID / Kode Request</label>
          <input class="form-input" type="text" id="booking_code" name="booking_code" placeholder="Contoh: ETH-20260528-00001 atau ETH-REQ-2026-000001" required>
          <p class="booking-disclaimer mt-2 mb-0">Jika Anda booking lewat form, gunakan <strong>Case ID</strong> atau <strong>Kode Request</strong> dari halaman sukses booking.</p>
        </div>
      </div>
      <p class="booking-disclaimer text-danger mb-0" id="booking_status_lookup_error" hidden></p>
      <p class="booking-actions"><button class="cta booking-submit" id="booking_status_lookup_button" type="submit">Cek Status Booking</button></p>
    </form>

    <article class="booking-panel booking-status-panel" id="booking_status_result" hidden>
      <h3>Informasi Booking Anda</h3>
      <div class="booking-support-state neutral" id="booking_status_state">
        <strong id="booking_status_state_label">Status booking</strong>
        <span id="booking_status_state_subtitle">Data status booking terbaru akan tampil di sini.</span>
      </div>

      <div class="booking-detail-tabs" data-status-tabs>
        <div class="booking-tab-list">
          <button class="booking-tab is-active" data-status-tab="info">Informasi Detail</button>
          <button class="booking-tab" data-status-tab="billing">Tagihan & Pembayaran</button>
          <button class="booking-tab" data-status-tab="actions">Aksi</button>
        </div>

        <div class="booking-tab-panel is-active" data-status-panel="info">
          <div class="booking-status-grid mt-3">
            <div class="booking-status-item">
              <p class="booking-status-key">Case ID</p>
              <p class="booking-status-value" id="status_case_id">-</p>
            </div>
            <div class="booking-status-item">
              <p class="booking-status-key">Kode Request</p>
              <p class="booking-status-value" id="status_request_code">-</p>
            </div>
            <div class="booking-status-item">
              <p class="booking-status-key">Nama Customer</p>
              <p class="booking-status-value" id="status_customer_name">-</p>
            </div>
            <div class="booking-status-item">
              <p class="booking-status-key">No WhatsApp</p>
              <p class="booking-status-value" id="status_customer_phone">-</p>
            </div>
            <div class="booking-status-item">
              <p class="booking-status-key">Tanggal Acara</p>
              <p class="booking-status-value" id="status_event_date">-</p>
            </div>
            <div class="booking-status-item">
              <p class="booking-status-key">Sesi Acara</p>
              <p class="booking-status-value" id="status_event_session">-</p>
            </div>
            <div class="booking-status-item">
              <p class="booking-status-key">Paket</p>
              <p class="booking-status-value" id="status_package_name">-</p>
            </div>
            <div class="booking-status-item">
              <p class="booking-status-key">Tipe Paket</p>
              <p class="booking-status-value" id="status_package_type">-</p>
            </div>
            <div class="booking-status-item">
              <p class="booking-status-key">Harga Paket</p>
              <p class="booking-status-value" id="status_package_price">-</p>
            </div>
            <div class="booking-status-item booking-status-item-full">
              <p class="booking-status-key">Alamat Paket</p>
              <p class="booking-status-value" id="status_package_address">-</p>
            </div>
            <div class="booking-status-item booking-status-item-full">
              <p class="booking-status-key">Lokasi Acara</p>
              <p class="booking-status-value" id="status_location">-</p>
            </div>
            <div class="booking-status-item booking-status-item-full">
              <p class="booking-status-key">Detail Acara</p>
              <p class="booking-status-value booking-status-pre" id="status_event_detail">-</p>
            </div>
            <div class="booking-status-item booking-status-item-full">
              <p class="booking-status-key">Pin Lokasi</p>
              <p class="booking-status-value"><a href="#" target="_blank" rel="noopener" id="status_google_maps_pin_link">Lihat pin lokasi</a></p>
            </div>
          </div>
        </div>

        <div class="booking-tab-panel" data-status-panel="billing" hidden>
          <div id="billing_summary_wrap" class="mt-3">
            <div class="booking-status-grid">
              <div class="booking-status-item">
                <p class="booking-status-key">Status Billing</p>
                <p class="booking-status-value" id="status_billing_status">Belum ada data pembayaran.</p>
              </div>
              <div class="booking-status-item">
                <p class="booking-status-key">Total Tagihan</p>
                <p class="booking-status-value" id="status_billing_total">-</p>
              </div>
              <div class="booking-status-item">
                <p class="booking-status-key">Total Dibayar</p>
                <p class="booking-status-value" id="status_billing_paid">-</p>
              </div>
              <div class="booking-status-item">
                <p class="booking-status-key">Sisa Pembayaran</p>
                <p class="booking-status-value" id="status_billing_remaining">-</p>
              </div>
            </div>
          </div>

          <div id="billing_details_wrap" class="mt-3 pt-3" hidden>
            <p class="booking-estimate-label mb-2">Komponen Biaya</p>
            <div id="billing_details_list"></div>
          </div>

          <div id="billing_installments_wrap" class="mt-3 pt-3" hidden>
            <p class="booking-estimate-label mb-2">Detail Tagihan</p>
            <div id="billing_installments_list"></div>
          </div>

          <div id="billing_history_wrap" class="mt-3 pt-3">
            <p class="booking-estimate-label mb-2">Riwayat Status</p>
            <ul class="booking-status-history" id="booking_status_history"></ul>
          </div>
        </div>

        <div class="booking-tab-panel" data-status-panel="actions" hidden>
          <div class="mt-3 mb-3">
            <button type="button" class="cta" id="btn_refresh_status" style="width:100%;">
              Refresh Data Booking
            </button>
          </div>

          <div class="mt-3" id="customer_actions_wrap">
            <p class="booking-estimate-label mb-2">Aksi yang Tersedia</p>
            <div id="customer_actions_list"></div>
          </div>

          <div class="mt-3 pt-3">
            <p class="booking-estimate-label mb-2">Butuh Bantuan?</p>
            <p class="booking-disclaimer mb-2">Hubungi tim kami via WhatsApp untuk pertanyaan atau bantuan terkait booking Anda.</p>
            <a class="cta" href="#" target="_blank" rel="noopener" id="btn_wa_support">
              <i class="ri-whatsapp-line me-1"></i> Hubungi via WhatsApp
            </a>
          </div>

          <div class="booking-support-actions mt-3">
            <a class="cta cta-outline" href="#" id="status_download_proof" hidden>Unduh Bukti Pengajuan (PDF)</a>
          </div>
        </div>
      </div>
    </article>
  </div>

  <div class="booking-confirm-modal" id="booking_status_verify_modal" hidden>
    <div class="booking-confirm-backdrop" data-booking-status-verify-close></div>
    <div class="booking-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="booking_status_verify_title">
      <div class="booking-confirm-header">
        <h4 id="booking_status_verify_title">Verifikasi Data Booking</h4>
        <button class="booking-confirm-close" type="button" aria-label="Tutup verifikasi" data-booking-status-verify-close>&times;</button>
      </div>
      <p class="booking-caption mb-2">Masukkan 4 digit terakhir nomor WhatsApp yang dipakai saat booking.</p>
      <div class="form-field mb-0">
        <label class="form-label" for="booking_status_phone_last4">4 Digit Terakhir No WhatsApp</label>
        <input class="form-input" type="text" id="booking_status_phone_last4" inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="Contoh: 1234" required>
      </div>
      <p class="booking-disclaimer text-danger mt-2 mb-0" id="booking_status_verify_error" hidden></p>
      <div class="booking-confirm-actions">
        <button class="cta cta-outline" type="button" data-booking-status-verify-close>Batal</button>
        <button class="cta" type="button" id="booking_status_verify_submit">Verifikasi & Tampilkan</button>
      </div>
    </div>
  </div>

  <div class="booking-confirm-modal" id="upload_payment_modal" hidden>
    <div class="booking-confirm-backdrop" data-upload-payment-close></div>
    <div class="booking-confirm-dialog booking-confirm-dialog-wide" role="dialog" aria-modal="true">
      <div class="booking-confirm-header">
        <h4 id="upload_payment_modal_title">Upload Bukti Pembayaran</h4>
        <button class="booking-confirm-close" type="button" aria-label="Tutup" data-upload-payment-close>&times;</button>
      </div>
      <form id="upload_payment_form" enctype="multipart/form-data">
        <input type="hidden" name="billing_installment_id" id="upload_payment_installment_id">
        <div class="mb-2">
          <div class="estimate-box mb-2">
            <p class="estimate-note mb-0" id="upload_payment_amount_info">Nominal: -</p>
          </div>
        </div>
        <div class="form-field mb-2">
          <label class="form-label" for="upload_payment_receipt">Bukti Transfer <span class="text-danger">*</span></label>
          <input class="form-input" type="file" id="upload_payment_receipt" name="transfer_receipt" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
          <p class="booking-disclaimer mt-1 mb-0">Wajib sertakan bukti transfer. Format: JPG, PNG, WebP, PDF. Maks 10MB.</p>
        </div>
        <p class="booking-disclaimer text-danger mb-0" id="upload_payment_error" hidden></p>
        <div class="booking-confirm-actions">
          <button class="cta cta-outline" type="button" data-upload-payment-close>Batal</button>
          <button class="cta" type="submit" id="upload_payment_submit_btn">Kirim Bukti Pembayaran</button>
        </div>
      </form>
    </div>
  </div>

  @include('pages.public.booking-page.sections.support-links')
</section>
@endsection
