<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pengajuan Booking - Etherno</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 13px;
            color: #1a1916;
            line-height: 1.6;
            background: #fff;
        }

        .pdf-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 40px 35px;
            background: #fff;
        }

        .pdf-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #b58f42;
        }

        .pdf-header-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .pdf-header-logo {
            width: 55px;
            height: 55px;
            object-fit: contain;
        }

        .pdf-header-brand-text {
            display: flex;
            flex-direction: column;
        }

        .pdf-header-brand-name {
            font-size: 28px;
            font-weight: 700;
            color: #1a1814;
            font-family: DejaVu Serif, Times New Roman, serif;
            letter-spacing: 0.5px;
        }

        .pdf-header-brand-subtitle {
            font-size: 11px;
            color: rgba(15,15,15,.6);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 2px;
        }

        .pdf-header-meta {
            text-align: right;
        }

        .pdf-header-meta-label {
            font-size: 10px;
            color: rgba(15,15,15,.6);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 4px;
        }

        .pdf-header-meta-value {
            font-size: 14px;
            color: #1a1916;
            font-weight: 700;
        }

        .pdf-title-section {
            margin-bottom: 28px;
            padding: 20px 24px;
            background: linear-gradient(135deg, rgba(181,143,66,.12), rgba(201,161,84,.08));
            border-left: 4px solid #b58f42;
            border-radius: 8px;
        }

        .pdf-title {
            font-size: 22px;
            font-weight: 700;
            color: #1a1814;
            font-family: DejaVu Serif, Times New Roman, serif;
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }

        .pdf-subtitle {
            font-size: 12px;
            color: rgba(15,15,15,.65);
            font-weight: 500;
            line-height: 1.5;
        }

        .pdf-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .pdf-meta-item {
            padding: 14px 16px;
            border: 1.5px solid rgba(181,143,66,.25);
            border-radius: 10px;
            background: linear-gradient(180deg, rgba(255,254,251,.95), rgba(255,251,245,.92));
        }

        .pdf-meta-item-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            color: #7a6025;
            margin-bottom: 6px;
        }

        .pdf-meta-item-value {
            font-size: 14px;
            color: #1a1916;
            font-weight: 700;
            word-break: break-word;
        }

        .pdf-section {
            margin-bottom: 24px;
        }

        .pdf-section-title {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #7a6025;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 2px solid rgba(181,143,66,.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pdf-section-icon {
            width: 18px;
            height: 18px;
            stroke: #b58f42;
            stroke-width: 2;
        }

        .pdf-data-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px 20px;
            margin-bottom: 20px;
        }

        .pdf-data-row {
            padding: 10px 14px;
            border: 1px solid rgba(15,15,15,.1);
            border-radius: 8px;
            background: rgba(255,255,255,.8);
        }

        .pdf-data-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: rgba(15,15,15,.6);
            margin-bottom: 4px;
        }

        .pdf-data-value {
            font-size: 13px;
            color: #1a1916;
            font-weight: 600;
            word-break: break-word;
            line-height: 1.5;
        }

        .pdf-data-value.muted {
            color: rgba(15,15,15,.75);
            font-weight: 500;
        }

        .pdf-data-full {
            grid-column: 1 / -1;
        }

        .pdf-notes-section {
            margin-top: 30px;
            padding: 18px 20px;
            border: 2px dashed rgba(181,143,66,.4);
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(255,249,238,.85), rgba(255,251,243,.82));
        }

        .pdf-notes-title {
            font-size: 13px;
            font-weight: 700;
            color: #3a2d13;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pdf-notes-icon {
            width: 16px;
            height: 16px;
            stroke: #b58f42;
            stroke-width: 2;
        }

        .pdf-notes-text {
            font-size: 12px;
            color: rgba(15,15,15,.75);
            line-height: 1.6;
            font-weight: 500;
        }

        .pdf-footer {
            margin-top: 35px;
            padding-top: 20px;
            border-top: 1px solid rgba(181,143,66,.3);
            text-align: center;
        }

        .pdf-footer-text {
            font-size: 11px;
            color: rgba(15,15,15,.6);
            font-weight: 500;
        }

        .pdf-footer-brand {
            font-size: 12px;
            font-weight: 700;
            color: #b58f42;
            margin-top: 4px;
            font-family: DejaVu Serif, Times New Roman, serif;
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        <div class="pdf-header">
            <div class="pdf-header-brand">
                @php
                    $logoPath = public_path('assets/etherno/public/icon_trans_2.png');
                    $logoData = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
                @endphp
                @if($logoData !== '')
                    <img src="{{ $logoData }}" alt="Etherno" class="pdf-header-logo">
                @endif
                <div class="pdf-header-brand-text">
                    <div class="pdf-header-brand-name">Etherno</div>
                    <div class="pdf-header-brand-subtitle">Wedding & Event Documentation</div>
                </div>
            </div>
            <div class="pdf-header-meta">
                <div class="pdf-header-meta-label">Tertanda</div>
                <div class="pdf-header-meta-value">{{ $submitted_at_label }}</div>
            </div>
        </div>

        <div class="pdf-title-section">
            <h1 class="pdf-title">Bukti Pengajuan Booking</h1>
            <p class="pdf-subtitle">Dokumen ini merupakan bukti pengajuan awal booking dan belum menjadi konfirmasi slot final. Tim Etherno akan melakukan review data terlebih dahulu.</p>
        </div>

        <div class="pdf-meta-grid">
            <div class="pdf-meta-item">
                <div class="pdf-meta-item-label">Case ID</div>
                <div class="pdf-meta-item-value">{{ $booking_case_id }}</div>
            </div>
            <div class="pdf-meta-item">
                <div class="pdf-meta-item-label">Kode Request</div>
                <div class="pdf-meta-item-value">{{ $request_code }}</div>
            </div>
            <div class="pdf-meta-item">
                <div class="pdf-meta-item-label">Waktu Pengajuan</div>
                <div class="pdf-meta-item-value">{{ $submitted_at_label }}</div>
            </div>
        </div>

        <div class="pdf-section">
            <div class="pdf-section-title">
                <svg class="pdf-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Informasi Customer
            </div>
            <div class="pdf-data-grid">
                <div class="pdf-data-row">
                    <div class="pdf-data-label">Nama Lengkap</div>
                    <div class="pdf-data-value">{{ $customer_name }}</div>
                </div>
                <div class="pdf-data-row">
                    <div class="pdf-data-label">No WhatsApp</div>
                    <div class="pdf-data-value">{{ $customer_phone }}</div>
                </div>
            </div>
        </div>

        <div class="pdf-section">
            <div class="pdf-section-title">
                <svg class="pdf-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                Jadwal Acara
            </div>
            <div class="pdf-data-grid">
                <div class="pdf-data-row">
                    <div class="pdf-data-label">Tanggal Acara</div>
                    <div class="pdf-data-value">{{ $booking_date_label }}</div>
                </div>
                <div class="pdf-data-row">
                    <div class="pdf-data-label">Sesi Acara</div>
                    <div class="pdf-data-value">{{ $event_session }}</div>
                </div>
            </div>
        </div>

        <div class="pdf-section">
            <div class="pdf-section-title">
                <svg class="pdf-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                Detail Paket
            </div>
            <div class="pdf-data-grid">
                <div class="pdf-data-row">
                    <div class="pdf-data-label">Nama Paket</div>
                    <div class="pdf-data-value">{{ $package_name }}</div>
                </div>
                <div class="pdf-data-row">
                    <div class="pdf-data-label">Tipe Paket</div>
                    <div class="pdf-data-value">{{ $package_type }}</div>
                </div>
                <div class="pdf-data-row pdf-data-full">
                    <div class="pdf-data-label">Harga Paket Dasar</div>
                    <div class="pdf-data-value">{{ $package_price }}</div>
                </div>
                <div class="pdf-data-row pdf-data-full">
                    <div class="pdf-data-label">Alamat Paket</div>
                    <div class="pdf-data-value muted">{{ $package_address }}</div>
                </div>
            </div>
        </div>

        <div class="pdf-section">
            <div class="pdf-section-title">
                <svg class="pdf-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                Lokasi Acara
            </div>
            <div class="pdf-data-grid">
                <div class="pdf-data-row pdf-data-full">
                    <div class="pdf-data-label">Lokasi Acara</div>
                    <div class="pdf-data-value">{{ $location_label }}</div>
                </div>
                <div class="pdf-data-row pdf-data-full">
                    <div class="pdf-data-label">Pin Google Maps</div>
                    <div class="pdf-data-value">{{ $google_maps_pin }}</div>
                </div>
            </div>
        </div>

        <div class="pdf-section">
            <div class="pdf-section-title">
                <svg class="pdf-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                Detail Acara
            </div>
            <div class="pdf-data-row pdf-data-full">
                <div class="pdf-data-label">Deskripsi Acara</div>
                <div class="pdf-data-value muted">{!! nl2br(e($event_detail)) !!}</div>
            </div>
        </div>

        <div class="pdf-notes-section">
            <div class="pdf-notes-title">
                <svg class="pdf-notes-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                Catatan Penting
            </div>
            <p class="pdf-notes-text">
                Slot dianggap terkunci setelah proses DP diverifikasi oleh admin. Batas waktu pembayaran DP adalah maksimal 3 hari setelah approval. Tim Etherno akan menghubungi Anda via WhatsApp untuk konfirmasi lebih lanjut.
            </p>
        </div>

        <div class="pdf-footer">
            <p class="pdf-footer-text">Generated by Etherno Booking System</p>
            <p class="pdf-footer-brand">Etherno - Professional Documentation</p>
        </div>
    </div>
</body>
</html>
