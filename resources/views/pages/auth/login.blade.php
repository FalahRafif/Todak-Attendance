@extends('layouts.auth.auth')

@section('title', $title ?? 'Login — KlikAbsen')

@push('styles')
<style>
    .login-page {
        min-height: 100vh;
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(420px, .95fr);
        background:
            radial-gradient(circle at 20% 20%, rgba(29, 155, 240, .22), transparent 28%),
            linear-gradient(135deg, #071827 0%, #0b2742 48%, #f8fbff 48.1%, #ffffff 100%);
    }

    .brand-panel {
        position: relative;
        display: flex;
        align-items: center;
        padding: 72px;
        color: #ffffff;
        overflow: hidden;
    }

    .brand-panel::after {
        content: "";
        position: absolute;
        width: 380px;
        height: 380px;
        right: -120px;
        bottom: -120px;
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 999px;
    }

    .brand-content {
        position: relative;
        z-index: 1;
        max-width: 560px;
    }

    .brand-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 999px;
        background: rgba(255,255,255,.08);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .brand-mark {
        display: inline-grid;
        place-items: center;
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background: #ffffff;
        color: var(--primary);
        font-weight: 900;
        box-shadow: 0 16px 40px rgba(0,0,0,.18);
    }

    .brand-title {
        margin: 30px 0 18px;
        font-size: clamp(44px, 6vw, 72px);
        line-height: .95;
        letter-spacing: -.05em;
        font-weight: 900;
    }

    .brand-title span {
        color: #7dd3fc;
    }

    .brand-copy {
        max-width: 460px;
        color: rgba(255,255,255,.72);
        font-size: 17px;
        line-height: 1.8;
        margin: 0;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 42px;
    }

    .feature-card {
        padding: 18px;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.08);
        backdrop-filter: blur(10px);
    }

    .feature-value {
        font-size: 24px;
        font-weight: 900;
        margin-bottom: 4px;
    }

    .feature-label {
        color: rgba(255,255,255,.62);
        font-size: 12px;
        line-height: 1.5;
    }

    .form-panel {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px;
    }

    .login-card {
        width: 100%;
        max-width: 440px;
        padding: 42px;
        border: 1px solid var(--line);
        border-radius: 28px;
        background: rgba(255,255,255,.94);
        box-shadow: 0 24px 70px rgba(15, 23, 42, .12);
    }

    .login-kicker {
        color: var(--accent);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .login-title {
        margin: 0;
        color: var(--ink);
        font-size: 34px;
        line-height: 1.12;
        letter-spacing: -.035em;
        font-weight: 900;
    }

    .login-subtitle {
        margin: 12px 0 30px;
        color: var(--muted);
        line-height: 1.7;
        font-size: 14px;
    }

    .alert {
        padding: 13px 14px;
        border-radius: 14px;
        margin-bottom: 18px;
        font-size: 14px;
        line-height: 1.5;
        border: 1px solid #fecaca;
        color: #991b1b;
        background: #fff1f2;
    }

    .field-stack {
        display: grid;
        gap: 16px;
    }

    .field label {
        display: block;
        margin-bottom: 8px;
        color: #1e293b;
        font-size: 13px;
        font-weight: 800;
    }

    .input {
        width: 100%;
        height: 50px;
        border: 1px solid var(--line);
        border-radius: 15px;
        padding: 0 15px;
        color: var(--ink);
        background: #ffffff;
        outline: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(29, 155, 240, .13);
    }

    .remember-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 18px 0 24px;
        color: var(--muted);
        font-size: 13px;
    }

    .remember-row label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .remember-row input {
        accent-color: var(--primary);
    }

    .submit-btn {
        width: 100%;
        height: 52px;
        border: 0;
        border-radius: 16px;
        color: #ffffff;
        cursor: pointer;
        font-weight: 900;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        box-shadow: 0 18px 34px rgba(15, 76, 129, .28);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .submit-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 22px 40px rgba(15, 76, 129, .34);
    }

    .login-note {
        margin-top: 22px;
        color: var(--muted);
        font-size: 12px;
        text-align: center;
        line-height: 1.6;
    }

    @media (max-width: 960px) {
        .login-page {
            grid-template-columns: 1fr;
            background: linear-gradient(180deg, #071827 0%, #0b2742 38%, #f8fbff 38.1%, #ffffff 100%);
        }

        .brand-panel {
            padding: 44px 28px 24px;
        }

        .feature-grid {
            grid-template-columns: 1fr;
        }

        .form-panel {
            padding: 28px;
        }
    }
</style>
@endpush

@section('content')
<main class="login-page">
    <section class="brand-panel">
        <div class="brand-content">
            <div class="brand-badge">
                <span class="brand-mark">KA</span>
                KlikAbsen Workforce System
            </div>
            <h1 class="brand-title">Absensi kerja <span>lebih presisi.</span></h1>
            <p class="brand-copy">Kelola kehadiran karyawan, jadwal kerja, lokasi kantor, izin, cuti, dan approval HRD dalam satu panel profesional.</p>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-value">GPS</div>
                    <div class="feature-label">Validasi lokasi dan radius kantor.</div>
                </div>
                <div class="feature-card">
                    <div class="feature-value">HRD</div>
                    <div class="feature-label">Approval izin, cuti, dan koreksi.</div>
                </div>
                <div class="feature-card">
                    <div class="feature-value">Live</div>
                    <div class="feature-label">Monitoring absensi harian.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="form-panel">
        <div class="login-card">
            <div class="login-kicker">Secure Login</div>
            <h2 class="login-title">Masuk ke KlikAbsen</h2>
            <p class="login-subtitle">Gunakan akun internal Anda untuk mengakses dashboard Admin atau HRD.</p>

            @if ($errors->any())
                <div class="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" autocomplete="off">
                @csrf
                <div class="field-stack">
                    <div class="field">
                        <label for="email">Email</label>
                        <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="admin@klikabsen.local" required autofocus>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input class="input" id="password" type="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>

                <div class="remember-row">
                    <label>
                        <input type="checkbox" name="remember" value="1">
                        Ingat saya
                    </label>
                </div>

                <button class="submit-btn" type="submit">Masuk Dashboard</button>
            </form>

            <div class="login-note">Default dev: admin@klikabsen.local / password</div>
        </div>
    </section>
</main>
@endsection
