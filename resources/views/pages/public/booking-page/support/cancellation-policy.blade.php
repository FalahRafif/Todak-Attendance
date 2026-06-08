@extends('layouts.guest.guest')

@section('content')
<section class="section-block container booking-section">
  <div class="section-heading booking-heading">
    <p class="eyebrow">Kebijakan Booking</p>
    <h2>Aturan DP, reschedule, cancellation, dan force majeure</h2>
    <p class="section-lead">Silakan baca kebijakan berikut sebelum melakukan pembayaran atau perubahan jadwal.</p>
  </div>

  <div class="booking-grid booking-grid-single">
    <article class="booking-panel">
      <h3>Kebijakan Utama</h3>
      <ul class="estimate-list">
        <li>Booking dianggap fix setelah DP dibayar dan diverifikasi admin.</li>
        <li>DP wajib dibayarkan maksimal H+3 setelah approval.</li>
        <li>Reschedule maksimal 14 hari sebelum acara dan mengikuti ketersediaan slot.</li>
        <li>Pembatalan setelah DP menyebabkan DP hangus (non-refundable).</li>
        <li>Jika fotografer berhalangan, Etherno menyiapkan pengganti tanpa biaya tambahan.</li>
        <li>Force majeure dapat diproses dengan penggantian jadwal atau refund setelah biaya operasional.</li>
      </ul>

      <div class="estimate-box">
        <p class="estimate-title">Koordinasi Lanjutan</p>
        <p class="estimate-note">Semua koordinasi lanjutan tetap diproses melalui WhatsApp agar cepat dan terdokumentasi.</p>
      </div>
    </article>
  </div>

  @include('pages.public.booking-page.sections.support-links')
</section>
@endsection

