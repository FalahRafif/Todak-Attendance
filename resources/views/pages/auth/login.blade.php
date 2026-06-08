@extends('layouts.auth.auth')

@section('title', $title)

@push('styles')
<style>
    .auth-page {
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 100vh;
    }

    /* ── Left dark cover ── */
    .auth-cover {
        background: linear-gradient(135deg, #11100e 0%, #1a1713 55%, #0d0c0b 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 56px 48px;
        position: relative;
        overflow: hidden;
    }
    .auth-cover::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at 30% 50%, rgba(181,143,66,.07) 0%, transparent 68%);
        pointer-events: none;
    }
    .auth-cover-inner {
        position: relative;
        z-index: 1;
        max-width: 380px;
    }
    .auth-cover-logo {
        height: 52px;
        width: auto;
        border-radius: 10px;
        object-fit: cover;
        margin-bottom: 28px;
    }
    .auth-cover-eyebrow {
        font-size: 11px;
        letter-spacing: .22em;
        text-transform: uppercase;
        color: rgba(181,143,66,.8);
        margin: 0 0 12px;
        font-weight: 700;
    }
    .auth-cover-title {
        font-family: 'Playfair Display', Georgia, 'Times New Roman', serif;
        font-size: clamp(38px, 3.8vw, 52px);
        line-height: 1.05;
        letter-spacing: -.025em;
        color: #fff;
        margin: 0 0 14px;
        text-shadow: 0 12px 36px rgba(0,0,0,.16);
    }
    .auth-cover-sep {
        width: 72px;
        height: 1px;
        background: rgba(255,255,255,.35);
        margin: 18px 0 20px;
    }
    .auth-cover-subtitle {
        color: rgba(255,255,255,.6);
        font-size: 15px;
        line-height: 1.72;
        margin: 0;
        max-width: 320px;
    }

    /* ── Right form side ── */
    .auth-form-side {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 56px 40px;
        background: #fbfaf8;
    }
    .auth-form-wrap {
        width: 100%;
        max-width: 400px;
    }
    .auth-eyebrow {
        font-size: 11px;
        letter-spacing: .22em;
        text-transform: uppercase;
        color: var(--gold);
        margin: 0 0 10px;
        font-weight: 700;
    }
    .auth-heading {
        font-family: 'Playfair Display', Georgia, 'Times New Roman', serif;
        font-size: clamp(34px, 3.2vw, 44px);
        line-height: 1.07;
        letter-spacing: -.02em;
        color: #121212;
        margin: 0 0 6px;
    }
    .auth-subheading {
        color: rgba(15,15,15,.58);
        font-size: 15px;
        line-height: 1.6;
        margin: 0;
    }
    .auth-form-sep {
        width: 48px;
        height: 1px;
        background: var(--gold);
        margin: 18px 0 28px;
    }

    /* ── Card ── */
    .auth-card {
        border: 1px solid rgba(15,15,15,.07);
        border-radius: 20px;
        background: linear-gradient(180deg, #fff 0%, #fdfcf9 100%);
        box-shadow: 0 18px 48px rgba(11,10,9,.06);
        padding: 34px 32px;
    }

    /* ── Fields ── */
    .auth-fields {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    .auth-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .auth-input-wrap .form-input {
        padding-right: 44px;
    }
    .auth-eye-btn {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        padding: 4px;
        cursor: pointer;
        color: rgba(15,15,15,.45);
        display: flex;
        align-items: center;
        line-height: 1;
        transition: color .15s ease;
    }
    .auth-eye-btn:hover { color: #121212; }
    .auth-eye-btn i { font-size: 16px; }

    .auth-footer-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 4px;
    }
    .auth-remember-label {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        color: rgba(15,15,15,.62);
        cursor: pointer;
        user-select: none;
    }
    .auth-remember-label input[type="checkbox"] {
        width: 14px;
        height: 14px;
        accent-color: var(--gold);
        cursor: pointer;
        flex-shrink: 0;
    }

    /* ── Submit ── */
    .auth-submit {
        display: block;
        width: 100%;
        border: none;
        cursor: pointer;
        font-family: inherit;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: .02em;
        margin-top: 26px;
        padding: 13px 20px;
        border-radius: 8px;
        background: var(--gold);
        color: #fff;
        box-shadow: 0 6px 18px rgba(11,10,9,.1);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .auth-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 26px rgba(11,10,9,.16);
    }

    /* ── Error ── */
    .auth-error {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 11px 14px;
        border: 1px solid rgba(184,80,80,.28);
        border-radius: 10px;
        background: rgba(254,236,236,.8);
        color: #9a3131;
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 18px;
    }
    .auth-error i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }

    /* ── Back link ── */
    .auth-back-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: rgba(15,15,15,.5);
        text-decoration: none;
        font-size: 13px;
        margin-top: 22px;
        transition: color .15s ease;
    }
    .auth-back-link:hover { color: #121212; }
    .auth-back-link i { font-size: 15px; }

    /* ── Responsive ── */
    @media (max-width: 860px) {
        .auth-page { grid-template-columns: 1fr; }
        .auth-cover { display: none; }
        .auth-form-side { padding: 48px 24px; min-height: 100vh; }
    }
    @media (max-width: 480px) {
        .auth-card { padding: 26px 20px; }
        .auth-form-side { padding: 36px 16px; }
    }
</style>
@endpush

@section('content')
<div class="auth-page">

    {{-- Left dark cover panel --}}
    <div class="auth-cover">
        <div class="auth-cover-inner">
            @php $logo = public_path('assets/etherno/public/icon_trans_white_1.png'); @endphp
            @if(file_exists($logo))
                <img src="{{ asset('assets/etherno/public/icon_trans_white_1.png') }}"
                     alt="Etherno"
                     class="auth-cover-logo">
            @endif

            <p class="auth-cover-eyebrow">Admin Panel</p>
            <h2 class="auth-cover-title">Etherno</h2>
            <div class="auth-cover-sep"></div>
            <p class="auth-cover-subtitle">
                Kelola booking, pembayaran, dan kalender sesi foto dari satu tempat.
            </p>
        </div>
    </div>

    {{-- Right form panel --}}
    <div class="auth-form-side">
        <div class="auth-form-wrap">

            <p class="auth-eyebrow">Selamat datang kembali</p>
            <h1 class="auth-heading">Masuk</h1>
            <p class="auth-subheading">Silahkan Masukan Email dan Password Anda Untuk Melanjutkan.</p>
            <div class="auth-form-sep"></div>

            <div class="auth-card">

                @if ($errors->any())
                    <div class="auth-error">
                        <i class="ri-error-warning-line"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <div class="auth-fields">
                        <div>
                            <label class="form-label" for="email">Email</label>
                            <input
                                class="form-input"
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="admin@etherno.id"
                                required
                                autofocus
                            >
                        </div>

                        <div>
                            <label class="form-label" for="password">Password</label>
                            <div class="auth-input-wrap">
                                <input
                                    class="form-input"
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="••••••••"
                                    required
                                >
                                <button class="auth-eye-btn" type="button" id="togglePassword" tabindex="-1" aria-label="Tampilkan password">
                                    <i class="ri-eye-line" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="auth-footer-row">
                            <label class="auth-remember-label">
                                <input type="checkbox" name="remember">
                                Ingat saya
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="auth-submit">Masuk</button>
                </form>

            </div>

            <a href="{{ route('home') }}" class="auth-back-link">
                <i class="ri-arrow-left-s-line"></i>
                Kembali ke halaman utama
            </a>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const toggleBtn = document.getElementById('togglePassword');
    const pwInput   = document.getElementById('password');
    const eyeIcon   = document.getElementById('eyeIcon');

    toggleBtn.addEventListener('click', () => {
        const isHidden   = pwInput.type === 'password';
        pwInput.type     = isHidden ? 'text' : 'password';
        eyeIcon.className = isHidden ? 'ri-eye-off-line' : 'ri-eye-line';
        toggleBtn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
    });
</script>
@endpush
