<footer class="public-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <div class="footer-brandline">
                @php
                    $icon = public_path('assets/etherno/public/icon_trans_white_1.png');
                    $fallback = public_path('assets/images/photos/aboutmain.jpg');
                @endphp
                @if(file_exists($icon))
                    <div class="footer-logo-wrap">
                        <img src="{{ asset('assets/etherno/public/icon_trans_white_1.png') }}" alt="Etherno" class="footer-logo">
                    </div>
                @elseif(file_exists($fallback))
                    <div class="footer-logo-wrap">
                        <img src="{{ asset('assets/images/photos/aboutmain.jpg') }}" alt="Etherno" class="footer-logo">
                    </div>
                @endif
                <div>
                    <p class="eyebrow footer-eyebrow">Etherno Pernikahan</p>
                    <p class="footer-text">Dokumentasi pernikahan dengan identitas visual yang sinematik dan elegan.</p>
                </div>
            </div>
        </div>

        <div class="footer-links">
            <p class="footer-title">Navigasi</p>
            <a href="{{ route('home') }}#portfolio">Portofolio</a>
            <a href="{{ route('home') }}#packages">Paket</a>
            <a href="{{ route('home') }}#faq">FAQ</a>
        </div>

        <div class="footer-links">
            <p class="footer-title">Kontak</p>
            <a href="{{ route('booking.page') }}">Pesan Sekarang</a>
            <a href="mailto:hello@etherno.id">hello@etherno.id</a>
            <a href="#">Instagram</a>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>&copy; {{ date('Y') }} Etherno. Crafted with ❤️ by mahasiswa Bina Sarana Informatika. Seluruh hak dilindungi.</span>
        <span>Dokumentasi pernikahan yang elegan.</span>
    </div>
</footer>
