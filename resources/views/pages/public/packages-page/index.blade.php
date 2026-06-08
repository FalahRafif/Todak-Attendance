@extends('layouts.guest.guest')

@section('content')
@php
  $weddingCollection = $weddingPackages ?? collect();
  $nonWeddingCollection = $nonWeddingPackages ?? collect();
  $totalPackages = $weddingCollection->count() + $nonWeddingCollection->count();

  $packageGroups = [
      [
          'title' => 'Wedding',
          'description' => 'Semua paket wedding aktif yang tersedia saat ini.',
          'packages' => $weddingCollection,
          'empty_message' => 'Belum ada paket wedding aktif.',
      ],
      [
          'title' => 'Non Wedding',
          'description' => 'Semua paket non wedding aktif yang tersedia saat ini.',
          'packages' => $nonWeddingCollection,
          'empty_message' => 'Belum ada paket non wedding aktif.',
      ],
  ];
@endphp

<section class="section-block container packages-page-section" id="all-packages">
  <div class="section-heading">
    <p class="eyebrow">Semua Produk</p>
    <h2>Daftar lengkap paket Etherno</h2>
    <p class="section-lead">Saat ini tersedia {{ $totalPackages }} paket aktif yang dapat dipilih customer untuk kebutuhan wedding maupun non wedding.</p>
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
            $benefits = $package->benefits->pluck('name')->filter()->values();
          @endphp
          <article class="package {{ $loop->index === 1 ? 'package-featured' : 'package-soft' }}">
            <p class="package-tag">{{ $loop->first ? 'Paling dipilih' : 'Pilihan ' . strtolower($group['title']) }}</p>
            <h3>{{ $package->name }}</h3>
            <div class="price">Rp {{ number_format((float) $package->price, 0, ',', '.') }}</div>
            <p class="package-copy">{{ $package->description ?: 'Deskripsi paket belum ditambahkan.' }}</p>
            <ul class="package-list">
              @forelse ($benefits as $benefit)
                <li>{{ $benefit }}</li>
              @empty
                <li>Benefit paket belum diisi.</li>
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

  <div class="packages-page-actions">
    <a class="cta cta-outline" href="{{ route('home') }}#packages">Kembali ke Landing</a>
    <a class="cta" href="{{ route('booking.page') }}">Lanjut Booking</a>
  </div>
</section>
@endsection
