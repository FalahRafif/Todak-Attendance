@extends('layouts.guest.guest')

@section('content')
<section class="section-block container booking-section">
  @php
    $requestCode = trim((string) session('booking_request_code', '-'));
    $bookingCaseId = trim((string) session('booking_case_id', '-'));
    $customerName = trim((string) session('booking_customer_name', 'Customer'));
    $proofDownloadUrl = trim((string) session('booking_proof_download_url', ''));
    $adminWhatsapp = trim((string) session('admin_whatsapp', ''));
    $whatsappTemplate = trim((string) session('whatsapp_template', ''));
  @endphp
  <div class="booking-grid booking-grid-single">
    <article class="booking-panel booking-support-card">
      <p class="eyebrow">Request Terkirim</p>
      <h2 class="final-cta-title booking-support-title">Request booking {{ $customerName }} sudah masuk</h2>
      <p class="booking-caption">Admin Etherno akan meninjau data terlebih dahulu sebelum tahap pembayaran DP. Booking belum dianggap fix sebelum DP diverifikasi.</p>

      <div class="booking-support-state success">
        <strong>OK</strong>
        <span>Status awal: <strong>WAITING APPROVAL</strong></span>
      </div>

      <div class="availability-summary mt-3">
        <strong>Case ID:</strong> {{ $bookingCaseId }}<br>
        <strong>Kode Request:</strong> {{ $requestCode }}
      </div>

      <div class="booking-form-grid booking-support-actions">
        @if($proofDownloadUrl !== '')
          <a class="cta cta-outline" href="{{ $proofDownloadUrl }}">Unduh Bukti Pengajuan (PDF)</a>
        @endif
        @if($adminWhatsapp !== '' && $whatsappTemplate !== '')
          <a class="cta" href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $adminWhatsapp)) }}?text={{ urlencode($whatsappTemplate) }}" target="_blank" rel="noopener">
            Hubungi Admin via WhatsApp
          </a>
        @endif
        <a class="cta cta-outline" href="{{ route('booking.status') }}">Cek Status Booking</a>
        <a class="cta cta-outline" href="{{ route('home') }}">Kembali ke Landing Page</a>
      </div>
    </article>
  </div>

  @include('pages.public.booking-page.sections.support-links')
</section>
@endsection
