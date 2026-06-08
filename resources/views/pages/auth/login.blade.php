@extends('layouts.auth.auth')

@section('title', $title ?? 'Login — KlikAbsen')

@push('styles')
<style>
    .login-page {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background: #f8fbff;
    }

    .brand-panel {
        position: relative;
        padding: 28px 20px 88px;
        color: #ffffff;
        background:
            radial-gradient(circle at 80% 0%, rgba(125, 211, 252, .30), transparent 32%),
            linear-gradient(145deg, #071827 0%, #0b2742 58%, #0f4c81 100%);
        overflow: hidden;
    }

    .brand-panel::after {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        right: -90px;
        bottom: -90px;
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 999px;
    }

    .brand-content {
        position: relative;
        z-index: 1;
        max-width: 560px;
        margin: 0 auto;
    }

    .brand-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 999px;
        background: rgba(255,255,255,.08);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .brand-mark {
        display: inline-grid;
        place-items: center;
        width: 32px;
        height: 32px;
        border-radius: 11px;
        background: #ffffff;
        color: var(--primary);
        font-weight: 900;
        box-shadow: 0 16px 40px rgba(0,0,0,.18);
    }

    .brand-title {
        margin: 24px 0 12px;
        font-size: clamp(34px, 12vw, 54px);
        line-height: .98;
        letter-spacing: -.055em;
        font-weight: 900;
    }

    .brand-title span {
        color: #7dd3fc;
    }

    .brand-copy {
        color: rgba(255,255,255,.74);
        font-size: 14px;
        line-height: 1.75;
        margin: 0;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-top: 22px;
    }

    .feature-card {
        padding: 12px;
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.08);
        backdrop-filter: blur(10px);
        min-width: 0;
    }

    .feature-value {
        font-size: 16px;
        font-weight: 900;
        margin-bottom: 3px;
    }

    .feature-label {
        color: rgba(255,255,255,.64);
        font-size: 10px;
        line-height: 1.4;
    }

    .form-panel {
        flex: 1;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 0 16px 24px;
        margin-top: -58px;
        position: relative;
        z-index: 2;
    }

    .login-card {
        width: 100%;
        max-width: 430px;
        padding: 26px 20px;
        border: 1px solid var(--line);
        border-radius: 24px;
        background: rgba(255,255,255,.98);
        box-shadow: 0 24px 70px rgba(15, 23, 42, .16);
    }

    .login-kicker {
        color: var(--accent);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .login-title {
        margin: 0;
        color: var(--ink);
        font-size: 28px;
        line-height: 1.12;
        letter-spacing: -.04em;
        font-weight: 900;
    }

    .login-subtitle {
        margin: 10px 0 24px;
        color: var(--muted);
        line-height: 1.65;
        font-size: 13px;
    }

    .alert {
        padding: 12px 13px;
        border-radius: 14px;
        margin-bottom: 16px;
        font-size: 13px;
        line-height: 1.5;
        border: 1px solid #fecaca;
        color: #991b1b;
        background: #fff1f2;
    }

    .field-stack {
        display: grid;
        gap: 14px;
    }

    .field label {
        display: block;
        margin-bottom: 7px;
        color: #1e293b;
        font-size: 13px;
        font-weight: 800;
    }

    .input {
        width: 100%;
        height: 52px;
        border: 1px solid var(--line);
        border-radius: 16px;
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
        margin: 16px 0 22px;
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
    }

    .login-note {
        margin-top: 18px;
        color: var(--muted);
        font-size: 12px;
        text-align: center;
        line-height: 1.6;
    }

    @media (min-width: 768px) {
        .brand-panel {
            padding: 44px 40px 116px;
        }

        .login-card {
            padding: 34px 32px;
        }

        .login-title {
            font-size: 34px;
        }
    }

    @media (min-width: 1024px) {
        .login-page {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(420px, .95fr);
            background:
                radial-gradient(circle at 20% 20%, rgba(29, 155, 240, .22), transparent 28%),
                linear-gradient(135deg, #071827 0%, #0b2742 48%, #f8fbff 48.1%, #ffffff 100%);
        }

        .brand-panel {
            display: flex;
            align-items: center;
            padding: 72px;
            background: transparent;
        }

        .brand-content {
            margin: 0;
        }

        .brand-title {
            font-size: clamp(54px, 5vw, 72px);
        }

        .brand-copy {
            font-size: 17px;
        }

        .feature-grid {
            gap: 14px;
            margin-top: 42px;
        }

        .feature-card {
            padding: 18px;
            border-radius: 20px;
        }

        .feature-value {
            font-size: 24px;
        }

        .feature-label {
            font-size: 12px;
        }

        .form-panel {
            align-items: center;
            padding: 48px;
            margin-top: 0;
        }

        .submit-btn {
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 40px rgba(15, 76, 129, .34);
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
