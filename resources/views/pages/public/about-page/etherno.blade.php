@extends('layouts.guest.guest')

@section('content')
<section class="section-block container booking-section">
  <div class="section-heading booking-heading">
    <p class="eyebrow">Tentang Kami</p>
    <h2>Etherno</h2>
    <p class="section-lead">Etherno adalah studio dokumentasi foto dan video untuk wedding maupun non-wedding, dengan gaya elegan, storytelling yang kuat, dan eksekusi rapi.</p>
  </div>

  <div class="booking-grid">
    <article class="booking-panel">
      <h3>Profil Etherno</h3>
      <p class="booking-caption">Kami menggabungkan pendekatan sinematik dengan fokus pada detail emosi dan momen utama. Setiap sesi dirancang agar klien memahami alur, biaya, serta kebutuhan teknis sejak awal.</p>

      <div class="estimate-box">
        <p class="estimate-title">Fokus Utama</p>
        <ul class="estimate-list">
          <li>Storytelling visual yang konsisten dan elegan.</li>
          <li>Proses booking transparan sebelum DP.</li>
          <li>Koordinasi cepat melalui WhatsApp.</li>
          <li>Output rapi dengan standar kualitas yang stabil.</li>
        </ul>
      </div>
    </article>

    <article class="booking-panel">
      <h3>Standar Layanan</h3>
      <p class="booking-caption">Kami menjaga komunikasi jelas, timeline rapi, dan pengalaman yang terasa profesional dari awal hingga hari acara.</p>

      <div class="estimate-box">
        <p class="estimate-title">Kenapa Etherno</p>
        <ul class="estimate-list">
          <li>Brief terstruktur sebelum hari H.</li>
          <li>Slot hanya terkunci setelah DP diverifikasi.</li>
          <li>Reschedule mengikuti ketersediaan tim.</li>
          <li>Tim berpengalaman untuk wedding dan non-wedding.</li>
        </ul>
      </div>

      <div class="booking-support-actions">
        <a class="cta booking-submit" href="{{ route('packages.page') }}">Lihat Paket</a>
        <a class="cta cta-outline" href="{{ route('booking.page') }}">Mulai Booking</a>
      </div>
    </article>
  </div>
</section>
@endsection
