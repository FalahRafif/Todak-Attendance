{{-- <section class="portfolio container" id="portfolio">
  @php $imgs = ['photos/9.jpg','photos/8.jpg','photos/7.jpg','photos/6.jpg','photos/5.jpg']; @endphp
  <div class="feature">
    @php $p = public_path('assets/images/' . $imgs[0]); @endphp
    @if(file_exists($p))
      <img src="{{ asset('assets/images/' . $imgs[0]) }}" alt="Foto unggulan portofolio">
    @else
      <img src="{{ asset('assets/images/icon.jpg') }}" alt="Pengganti portofolio">
    @endif
  </div>
  <div class="grid">
  @foreach(array_slice($imgs,1) as $img)
    @php $p = public_path('assets/images/' . $img); @endphp
    @if(file_exists($p))
      <div><img src="{{ asset('assets/images/' . $img) }}" alt="Portofolio"></div>
    @else
      <div><img src="{{ asset('assets/images/icon.jpg') }}" alt="Pengganti portofolio"></div>
    @endif
  @endforeach
  </div>
</section> --}}
