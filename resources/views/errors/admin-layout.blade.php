@php
    $errorCodeLabel = $statusCode !== '' ? $statusCode : 'ERR';
    $primaryActionLabel = $isAuthenticated ? 'Kembali ke Dashboard' : 'Masuk Admin';
    $primaryActionUrl = $isAuthenticated ? $dashboardUrl : $loginUrl;
@endphp

@include('layouts.admin.assets')

<body>
    <style>
        .internal-error-hero {
            margin-top: 1.25rem;
            margin-bottom: 1rem;
        }

        .internal-error-card {
            border: 1px solid rgba(17, 24, 39, 0.08);
            border-radius: 16px;
            box-shadow: 0 16px 38px rgba(17, 24, 39, 0.08);
            overflow: hidden;
        }

        .internal-error-banner {
            background: linear-gradient(135deg, #141b2f 0%, #1f2a44 50%, #0f1424 100%);
            color: #fff;
            padding: 2rem;
            position: relative;
        }

        .internal-error-banner::after {
            content: "";
            position: absolute;
            inset: auto 2rem 1.2rem;
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
        }

        .internal-error-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.76rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(236, 244, 255, 0.88);
            margin-bottom: 0.9rem;
        }

        .internal-error-title {
            margin: 0;
            font-size: clamp(1.65rem, 2.5vw, 2.55rem);
            line-height: 1.18;
            color: #fff;
        }

        .internal-error-message {
            margin-top: 1rem;
            margin-bottom: 0;
            color: rgba(240, 245, 255, 0.94);
            max-width: 62ch;
            line-height: 1.7;
        }

        .internal-error-hint {
            margin-top: 0.8rem;
            margin-bottom: 0;
            color: rgba(224, 232, 246, 0.82);
            font-size: 0.92rem;
        }

        .internal-error-code {
            min-height: 100%;
            display: grid;
            place-items: center;
            border-left: 1px solid rgba(255, 255, 255, 0.16);
            font-size: clamp(2.2rem, 4.3vw, 4rem);
            font-weight: 700;
            letter-spacing: 0.06em;
            color: rgba(255, 255, 255, 0.95);
        }

        .internal-error-body {
            padding: 1.4rem 1.5rem 1.5rem;
            background: #fff;
        }

        .internal-error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-bottom: 1rem;
        }

        .internal-error-note {
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .internal-error-help {
            height: 100%;
            border: 1px solid rgba(17, 24, 39, 0.08);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 10px 25px rgba(17, 24, 39, 0.06);
            padding: 1rem;
        }

        .internal-error-help h3 {
            font-size: 1rem;
            margin: 0 0 0.45rem;
        }

        .internal-error-help p {
            margin: 0;
            color: #6b7280;
            font-size: 0.88rem;
            line-height: 1.65;
        }

        @media (max-width: 991.98px) {
            .internal-error-code {
                border-left: 0;
                border-top: 1px solid rgba(255, 255, 255, 0.16);
                padding-top: 1rem;
                margin-top: 1rem;
            }

            .internal-error-banner {
                padding: 1.5rem;
            }
        }
    </style>

    @include('layouts.admin.switcher')

    <div class="page">
        @include('layouts.admin.header')
        @include('layouts.admin.sidebar')

        <div class="main-content app-content">
            <div class="main-container container-fluid">
                <div class="page-header d-flex flex-wrap align-items-start justify-content-between gap-3 internal-error-hero">
                    <div>
                        <h1 class="page-title mb-1">Status {{ $errorCodeLabel }} - {{ $statusTitle }}</h1>
                        <p class="text-muted mb-0">Halaman error ini otomatis menyesuaikan layout panel internal.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $homeUrl }}" class="btn btn-outline-primary btn-sm">Beranda</a>
                        <a href="{{ $bookingUrl }}" class="btn btn-outline-secondary btn-sm">Form Booking</a>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <section class="internal-error-card">
                            <div class="internal-error-banner">
                                <div class="row g-3 align-items-stretch">
                                    <div class="col-lg-9">
                                        <p class="internal-error-eyebrow">
                                            <span class="badge bg-warning-transparent text-warning">Panel {{ strtoupper($panelPrefix) }}</span>
                                            <span>Status {{ $errorCodeLabel }}</span>
                                        </p>
                                        <h2 class="internal-error-title">{{ $statusTitle }}</h2>
                                        <p class="internal-error-message">{{ $statusMessage }}</p>
                                        @if ($statusHint !== '')
                                            <p class="internal-error-hint">{{ $statusHint }}</p>
                                        @endif
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="internal-error-code">{{ $errorCodeLabel }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="internal-error-body">
                                <div class="internal-error-actions">
                                    <a href="{{ $primaryActionUrl }}" class="btn btn-primary">{{ $primaryActionLabel }}</a>
                                    <button type="button" onclick="window.location.reload()" class="btn btn-outline-primary">Muat Ulang</button>
                                    <a href="{{ $homeUrl }}" class="btn btn-light">Halaman Utama</a>
                                </div>
                                <p class="internal-error-note">Jika issue masih berulang, simpan waktu kejadian dan status code untuk memudahkan investigasi teknis.</p>
                            </div>
                        </section>
                    </div>

                    <div class="col-md-4">
                        <article class="internal-error-help">
                            <h3>Periksa URL</h3>
                            <p>Pastikan path halaman panel tidak salah ketik, termasuk prefix `admin` atau `petugas`.</p>
                        </article>
                    </div>
                    <div class="col-md-4">
                        <article class="internal-error-help">
                            <h3>Validasi Session</h3>
                            <p>Session login yang expired dapat memicu akses ditolak. Coba login ulang lalu buka halaman lagi.</p>
                        </article>
                    </div>
                    <div class="col-md-4">
                        <article class="internal-error-help">
                            <h3>Retry Bertahap</h3>
                            <p>Untuk error sementara seperti 429 atau 503, beri jeda singkat sebelum melakukan request ulang.</p>
                        </article>
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.admin.footer')
        @include('layouts.admin.rightsidebar')
    </div>

    @include('layouts.admin.scripts')
</body>
</html>
