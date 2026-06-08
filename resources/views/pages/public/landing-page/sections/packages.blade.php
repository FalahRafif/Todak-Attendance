@php
  $packageGroups = [
      [
          'title' => 'Paket Wedding',
          'description' => 'Dirancang untuk momen sakral Anda: alur terarah, dokumentasi elegan, dan cerita pernikahan yang utuh.',
          'packages' => $weddingPackages ?? collect(),
          'empty_message' => 'Paket wedding akan segera hadir kembali. Hubungi kami untuk rekomendasi tercepat.',
      ],
      [
          'title' => 'Paket Non Wedding',
          'description' => 'Fleksibel untuk engagement, wisuda, corporate, hingga event private dengan kualitas visual premium.',
          'packages' => $nonWeddingPackages ?? collect(),
          'empty_message' => 'Paket non wedding sedang kami kurasi ulang. Tim kami siap bantu pilih opsi terbaik.',
      ],
  ];
@endphp

<section class="section-block container" id="packages">
  <div class="section-heading">
    <p class="eyebrow">Paket Favorit Customer</p>
    <h2>Temukan paket yang paling pas untuk momen terbaik Anda</h2>
    <p class="section-lead">Setiap paket disusun agar proses booking mudah, hasil visual berkelas, dan value jelas sejak awal. Slot terbaik biasanya cepat terisi.</p>
  </div>

  @foreach ($packageGroups as $group)
    <div class="package-group">
      <div class="package-group-heading">
        <h3 class="package-group-title">{{ $group['title'] }}</h3>
        <p class="package-group-copy">{{ $group['description'] }}</p>
      </div>

      <div class="package-grid">
        @forelse (($group['packages'] ?? collect()) as $package)
          @php
            $benefits = $package->benefits->pluck('name')->filter()->take(4)->values();
            $packageTag = match (true) {
                $loop->first => 'Best Seller',
                $loop->index === 1 => 'Paling Direkomendasikan',
                default => 'Value Terbaik',
            };
          @endphp
          <article class="package {{ $loop->index === 1 ? 'package-featured' : 'package-soft' }}">
            {{-- <p class="package-tag">{{ $packageTag }}</p> --}}
            <p class="package-tag">pilihan paket</p>
            <h3>{{ $package->name }}</h3>
            <div class="price">Rp {{ number_format((float) $package->price, 0, ',', '.') }}</div>
            <p class="package-copy">{{ $package->description ?: 'Ideal untuk Anda yang ingin hasil dokumentasi rapi, emosional, dan siap dibagikan.' }}</p>
            <ul class="package-list">
              @forelse ($benefits as $benefit)
                <li>{{ $benefit }}</li>
              @empty
                <li>Benefit detail sedang diperbarui, konsultasi cepat tersedia via WhatsApp.</li>
              @endforelse
            </ul>
          </article>
        @empty
          <article class="package-empty">
            <p>{{ $group['empty_message'] }}</p>
          </article>
        @endforelse
      </div>
    </div>
  @endforeach

  <div class="packages-more">
    <a class="cta cta-outline packages-more-button" href="{{ route('packages.page') }}">Lihat Semua Paket & Benefit</a>
  </div>
</section>
