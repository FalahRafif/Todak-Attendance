@php
    $statusCode = trim($__env->yieldContent('status_code'));
    if ($statusCode === '' && isset($exception) && method_exists($exception, 'getStatusCode')) {
        $statusCode = (string) $exception->getStatusCode();
    }

    $statusTitle = trim($__env->yieldContent('status_title'));
    if ($statusTitle === '') {
        $statusTitle = 'Terjadi Kesalahan';
    }

    $statusMessage = trim($__env->yieldContent('status_message'));
    if ($statusMessage === '') {
        $statusMessage = 'Halaman yang Anda buka mengalami kendala. Silakan coba kembali beberapa saat lagi.';
    }

    $statusHint = trim($__env->yieldContent('status_hint'));

    $title = trim(($statusCode !== '' ? "{$statusCode} - " : '') . $statusTitle . ' - Etherno');

    $homeUrl = \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/');
    $bookingUrl = \Illuminate\Support\Facades\Route::has('booking.page') ? route('booking.page') : url('/booking');
@endphp

@include('layouts.public.assets')

<style>
    .error-main {
        background: #fbfaf8;
    }

    .error-hero {
        position: relative;
        background: linear-gradient(135deg, #11100e 0%, #1a1713 55%, #0d0c0b 100%);
        color: #fff;
        overflow: hidden;
        padding: 76px 0 72px;
    }

    .error-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at 20% 50%, rgba(181, 143, 66, 0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .error-hero-inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(260px, 0.9fr);
        gap: 34px;
        align-items: center;
    }

    .error-copy {
        max-width: 700px;
    }

    .error-eyebrow {
        margin: 0 0 10px;
        font-size: 11px;
        letter-spacing: .22em;
        text-transform: uppercase;
        color: rgba(181, 143, 66, .92);
        font-weight: 700;
    }

    .error-title {
        margin: 0;
        font-family: 'Playfair Display', Georgia, 'Times New Roman', serif;
        font-size: clamp(38px, 5vw, 62px);
        line-height: 1.02;
        letter-spacing: -.02em;
        color: #fff;
        text-shadow: 0 10px 32px rgba(0, 0, 0, .2);
    }

    .error-sep {
        width: 94px;
        height: 1px;
        background: rgba(255, 255, 255, .45);
        margin: 16px 0 18px;
    }

    .error-message {
        margin: 0;
        max-width: 620px;
        color: rgba(255, 255, 255, .9);
        font-size: 16px;
        line-height: 1.75;
    }

    .error-hint {
        margin: 12px 0 0;
        color: rgba(255, 255, 255, .72);
        font-size: 14px;
        line-height: 1.65;
    }

    .error-actions {
        margin-top: 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .error-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 8px;
        padding: 11px 18px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
    }

    .error-btn-primary {
        background: #b58f42;
        color: #fff;
        box-shadow: 0 8px 22px rgba(0, 0, 0, .16);
    }

    .error-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 26px rgba(0, 0, 0, .2);
    }

    .error-btn-ghost {
        background: rgba(255, 255, 255, .08);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .22);
    }

    .error-btn-ghost:hover {
        background: rgba(255, 255, 255, .16);
        transform: translateY(-1px);
    }

    .error-visual {
        display: grid;
        place-items: center;
    }

    .error-code {
        position: relative;
        display: grid;
        place-items: center;
        width: min(100%, 300px);
        aspect-ratio: 1/1;
        border-radius: 28px;
        border: 1px solid rgba(255, 255, 255, .16);
        background: linear-gradient(180deg, rgba(255, 255, 255, .08), rgba(255, 255, 255, .02));
        box-shadow: 0 30px 74px rgba(0, 0, 0, .28);
        font-family: 'Playfair Display', Georgia, 'Times New Roman', serif;
        font-size: clamp(66px, 11vw, 120px);
        line-height: 1;
        color: rgba(255, 255, 255, .96);
        letter-spacing: .02em;
    }

    .error-code::after {
        content: "";
        position: absolute;
        inset: auto 22px 22px;
        height: 1px;
        background: rgba(255, 255, 255, .18);
    }

    .error-support {
        padding: 52px 0 10px;
    }

    .error-support .section-heading {
        margin-bottom: 22px;
    }

    .error-support-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .error-support-card {
        border: 1px solid rgba(15, 15, 15, .06);
        border-radius: 16px;
        background: linear-gradient(180deg, #fff, #fcfbf9);
        box-shadow: 0 12px 30px rgba(11, 10, 9, .04);
        padding: 20px;
    }

    .error-support-card h3 {
        margin: 0 0 8px;
        font-family: 'Playfair Display', Georgia, 'Times New Roman', serif;
        font-size: 24px;
        line-height: 1.16;
        color: #121212;
    }

    .error-support-card p {
        margin: 0;
        color: rgba(15, 15, 15, .68);
        font-size: 14px;
        line-height: 1.7;
    }

    @media (max-width: 980px) {
        .error-hero-inner {
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .error-visual {
            justify-items: start;
        }

        .error-code {
            width: min(100%, 240px);
        }

        .error-support-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 680px) {
        .error-hero {
            padding: 62px 0;
        }

        .error-support-grid {
            grid-template-columns: 1fr;
        }

        .error-actions {
            gap: 10px;
        }
    }
</style>

@include('layouts.public.header')

<main class="public-main error-main">
    <section class="error-hero">
        <div class="container error-hero-inner">
            <div class="error-copy">
                <p class="error-eyebrow">Status {{ $statusCode !== '' ? $statusCode : 'Error' }}</p>
                <h1 class="error-title">{{ $statusTitle }}</h1>
                <div class="error-sep"></div>
                <p class="error-message">{{ $statusMessage }}</p>

                @if ($statusHint !== '')
                    <p class="error-hint">{{ $statusHint }}</p>
                @endif

                <div class="error-actions">
                    <a class="error-btn error-btn-primary" href="{{ $homeUrl }}">Kembali ke Beranda</a>
                    <a class="error-btn error-btn-ghost" href="{{ $bookingUrl }}">Buka Form Booking</a>
                    <button class="error-btn error-btn-ghost" type="button" onclick="window.location.reload()">Muat Ulang</button>
                </div>
            </div>

            <div class="error-visual" aria-hidden="true">
                <div class="error-code">{{ $statusCode !== '' ? $statusCode : 'ERR' }}</div>
            </div>
        </div>
    </section>

    <section class="section-block error-support">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Bantuan Cepat</p>
                <h2>Tetap Tenang, Kami Bantu Anda Lanjut</h2>
                <p class="section-lead">Jika kendala masih muncul, kembali ke halaman utama atau coba ulangi akses halaman beberapa saat lagi.</p>
            </div>

            <div class="error-support-grid">
                <article class="error-support-card">
                    <h3>Periksa URL</h3>
                    <p>Pastikan alamat halaman ditulis dengan benar, termasuk tanda hubung, huruf, dan parameter URL.</p>
                </article>
                <article class="error-support-card">
                    <h3>Muat Ulang</h3>
                    <p>Untuk gangguan sesaat, refresh halaman bisa membantu mengambil ulang resource terbaru dari server.</p>
                </article>
                <article class="error-support-card">
                    <h3>Kembali Ke Alur Utama</h3>
                    <p>Gunakan tombol Beranda atau Form Booking untuk kembali ke halaman inti yang paling sering digunakan.</p>
                </article>
            </div>
        </div>
    </section>
</main>

@include('layouts.public.footer')

@include('layouts.public.scripts')
