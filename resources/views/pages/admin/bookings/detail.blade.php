@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@php
    $booking = $booking ?? null;
    $caseId = $caseId ?? 'ETH-XXXX';
@endphp

@if (!$booking)
    <div class="page-header d-flex flex-wrap align-items-start justify-content-between gap-3 my-3">
        <div>
            <h1 class="page-title mb-1">Booking Detail</h1>
            <p class="text-muted mb-0">Booking dengan code <strong>{{ $caseId }}</strong> tidak ditemukan.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ panel_route('admin.bookings.list') }}" class="btn btn-outline-primary btn-sm">
                <i class="ri-arrow-left-line me-1"></i> Kembali ke Daftar Booking
            </a>
        </div>
    </div>
@else
@php
    $statusCode = $statusCode ?? '';
    $customerName = $customerName ?? '-';
    $actions = match($statusCode) {
        'BS_WAITING_APPROVAL' => [
            ['label' => 'Refresh Data', 'url' => request()->fullUrl(), 'class' => 'btn btn-outline-primary btn-sm'],
            ['label' => 'Kembali ke List', 'url' => panel_route('admin.bookings.list'), 'class' => 'btn btn-outline-secondary btn-sm'],
        ],
        default => [
            ['label' => 'Refresh Data', 'url' => request()->fullUrl(), 'class' => 'btn btn-outline-primary btn-sm'],
            ['label' => 'Kembali ke List', 'url' => panel_route('admin.bookings.list'), 'class' => 'btn btn-outline-secondary btn-sm'],
        ],
    };

    $alerts = [
        ['class' => 'alert-info', 'text' => 'Booking code: <strong>' . $caseId . '</strong>. Status: <strong>' . ($statusLabel ?? '-') . '</strong>.'],
    ];

    $stats = $stats ?? [];
    $availableActions = $availableActions ?? [];
    $locationChain = $locationChain ?? [];
    $locationPricingInfo = $locationPricingInfo ?? ['found' => false, 'label' => '-'];
    $googleMapsUrl = $googleMapsUrl ?? '';
    $packageBenefits = $packageBenefits ?? collect();
    $timeline = $timeline ?? [];
    $billing = $billing ?? null;
    $billingTools = $billingTools ?? [];
    $canManageBilling = (bool) ($billingTools['can_manage'] ?? false);
    $canAddBillingDetail = (bool) ($billingTools['can_add_detail'] ?? false);
    $canGenerateInstallment = (bool) ($billingTools['can_generate_installment'] ?? false);
    $dpVerified = (bool) ($billingTools['dp_verified'] ?? false);
    $dpInstallments = $billingTools['dp_installments'] ?? [];
    $nonDpInstallments = $billingTools['non_dp_installments'] ?? [];
    $installmentTypeOptions = $billingTools['installment_types'] ?? [];
    $defaultInstallmentDueDate = $billingTools['default_due_date'] ?? now()->format('Y-m-d');
@endphp

@include('pages.admin.partials.page-header', [
    'heading' => 'Booking Detail',
    'summary' => 'Detail operasional booking ' . $caseId . ' - ' . ($statusLabel ?? ''),
    'actions' => $actions,
])

@include('pages.admin.partials.alerts', ['alerts' => $alerts])
@include('pages.admin.partials.stats-grid', ['stats' => $stats])

<div class="row g-3">
    <div class="col-12 col-xl-7">
        @php
            $showBillingTab = in_array($statusCode, [
                'BS_APPROVED_WAITING_DP',
                'BS_APPROVED_WAITING_FINAL_PAYMENT',
                'BS_CONFIRMED',
                'BS_COMPLETE',
                'BS_EXPIRED_DP',
                'BS_CANCEL',
                'BS_REFUND',
                'BS_FORCE_MAJEURE',
            ], true);
        @endphp

        <div class="card custom-card mb-3">
            <div class="card-header pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0">Informasi Booking</h5>
                </div>

                <div class="booking-detail-main-tabs-wrap mt-3">
                    <ul class="nav nav-tabs booking-detail-main-tabs flex-nowrap" id="booking_detail_main_tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab_customer_event" data-bs-toggle="tab" data-bs-target="#pane_customer_event" type="button" role="tab" aria-controls="pane_customer_event" aria-selected="true">
                                Data Customer & Acara
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab_package_detail" data-bs-toggle="tab" data-bs-target="#pane_package_detail" type="button" role="tab" aria-controls="pane_package_detail" aria-selected="false">
                                Detail Paket
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab_location_schedule" data-bs-toggle="tab" data-bs-target="#pane_location_schedule" type="button" role="tab" aria-controls="pane_location_schedule" aria-selected="false">
                                Lokasi & Jadwal
                            </button>
                        </li>
                        @if($showBillingTab)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab_billing_payment" data-bs-toggle="tab" data-bs-target="#pane_billing_payment" type="button" role="tab" aria-controls="pane_billing_payment" aria-selected="false">
                                Billing & Pembayaran
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="card-body tab-content pt-3" id="booking_detail_main_tabs_content">
                <div class="tab-pane fade show active" id="pane_customer_event" role="tabpanel" aria-labelledby="tab_customer_event">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted ps-0" style="width: 180px;">Nama Lengkap</td>
                                    <td class="fw-semibold">{{ $customerName }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">No WhatsApp</td>
                                    <td>{{ $booking->customer?->phone_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Jenis Acara</td>
                                    <td><span class="badge bg-success-transparent text-success">{{ $booking->package?->packageType?->description ?? '-' }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Paket</td>
                                    <td class="fw-semibold">{{ $booking->package?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Harga Paket</td>
                                    <td class="fw-semibold">Rp {{ number_format((float) ($booking->package?->price ?? 0), 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Detail Acara</td>
                                    <td class="booking-detail-preline">{{ $booking->event_detail ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="pane_package_detail" role="tabpanel" aria-labelledby="tab_package_detail">
                    @if($booking->package)
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted ps-0" style="width: 180px;">Nama Paket</td>
                                        <td class="fw-semibold">{{ $booking->package->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-0">Tipe Paket</td>
                                        <td>{{ $booking->package->packageType?->description ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-0">Alamat Paket</td>
                                        <td>{{ $booking->package->address ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-0">Deskripsi</td>
                                        <td>{{ $booking->package->description ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @if($packageBenefits->isNotEmpty())
                            <hr class="my-3">
                            <h6 class="mb-2">Benefit Paket</h6>
                            <ul class="mb-0 ps-3">
                                @foreach($packageBenefits as $benefit)
                                    <li>- {{ $benefit->name }}@if($benefit->description) <small class="text-muted">- {{ $benefit->description }}</small>@endif</li>
                                @endforeach
                            </ul>
                        @endif
                    @else
                        <p class="text-muted mb-0">Data paket tidak tersedia.</p>
                    @endif
                </div>

                <div class="tab-pane fade" id="pane_location_schedule" role="tabpanel" aria-labelledby="tab_location_schedule">
                    <div class="d-flex flex-wrap justify-content-end mb-2">
                        @if($locationPricingInfo['found'] ?? false)
                            <span class="badge bg-info-transparent text-info">Aturan Harga: {{ $locationPricingInfo['label'] ?? '-' }}</span>
                        @else
                            <span class="badge bg-warning-transparent text-warning">Aturan Harga: {{ $locationPricingInfo['label'] ?? 'Tidak ditemukan' }}</span>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted ps-0" style="width: 180px;">Tanggal Acara</td>
                                    <td class="fw-semibold">{{ $booking->event_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Sesi</td>
                                    <td class="fw-semibold">{{ $booking->eventSession?->description ?? '-' }}</td>
                                </tr>
                                @forelse($locationChain as $loc)
                                    <tr>
                                        <td class="text-muted ps-0">{{ $loc['level'] }}</td>
                                        <td>{{ $loc['name'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted ps-0">Lokasi</td>
                                        <td>{{ $booking->location?->name ?? '-' }}</td>
                                    </tr>
                                @endforelse
                                <tr>
                                    <td class="text-muted ps-0">Pin Google Maps</td>
                                    <td>
                                        @if($googleMapsUrl !== '')
                                            <a href="{{ $googleMapsUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-map-pin-line me-1"></i> Buka di Google Maps
                                            </a>
                                        @else
                                            <span class="text-muted">Tidak tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($googleMapsUrl !== '' && preg_match('/q=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $googleMapsUrl, $coords))
                        <div class="mt-3 rounded overflow-hidden border" style="height: 250px;">
                            <iframe
                                width="100%"
                                height="100%"
                                frameborder="0"
                                style="border:0"
                                src="https://maps.google.com/maps?q={{ $coords[1] }},{{ $coords[2] }}&z=15&output=embed"
                                allowfullscreen>
                            </iframe>
                        </div>
                    @endif
                </div>

                @if($showBillingTab)
                <div class="tab-pane fade" id="pane_billing_payment" role="tabpanel" aria-labelledby="tab_billing_payment">
                    @if($billing)
                        @php
                            $totalAmount = (float) ($billing['total_amount'] ?? 0);
                            $totalPaid = (float) ($billing['total_paid'] ?? 0);
                            $remainingAmount = (float) ($billing['remaining_amount'] ?? 0);
                            $totalDetailAmount = (float) ($billing['total_detail_amount'] ?? 0);
                            $totalInstallmentAmount = (float) ($billing['total_installment_amount'] ?? 0);
                            $unallocatedAmount = (float) ($billing['unallocated_amount'] ?? 0);
                        @endphp

                        <div class="alert alert-light border border-primary-subtle mb-3">
                            <p class="fw-semibold mb-2">Panduan Singkat Billing</p>
                            <div class="small text-muted mb-1">
                                <strong>Billing Summary</strong> menampilkan total keseluruhan biaya customer.
                            </div>
                            <div class="small text-muted mb-1">
                                <strong>Billing Details</strong> berisi rincian komponen biaya seperti paket dasar dan add-on.
                            </div>
                            <div class="small text-muted mb-0">
                                <strong>Billing Installments</strong> adalah tagihan per termin yang dikirim ke customer dan bisa ditambah saat ada komponen biaya baru.
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-4">
                                <div class="border rounded p-2 h-100">
                                    <p class="text-muted mb-1 small">Total Billing</p>
                                    <p class="fw-semibold mb-0">Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="border rounded p-2 h-100">
                                    <p class="text-muted mb-1 small">Total Dibayar</p>
                                    <p class="fw-semibold text-success mb-0">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="border rounded p-2 h-100">
                                    <p class="text-muted mb-1 small">Sisa Pembayaran</p>
                                    <p class="fw-semibold text-danger mb-0">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="border rounded p-2 h-100">
                                    <p class="text-muted mb-1 small">Total Komponen Biaya</p>
                                    <p class="fw-semibold mb-0">Rp {{ number_format($totalDetailAmount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="border rounded p-2 h-100">
                                    <p class="text-muted mb-1 small">Total Tagihan Terjadwal</p>
                                    <p class="fw-semibold mb-0">Rp {{ number_format($totalInstallmentAmount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="border rounded p-2 h-100">
                                    <p class="text-muted mb-1 small">Sisa Belum Dijadwalkan</p>
                                    <p class="fw-semibold {{ $unallocatedAmount > 0 ? 'text-warning' : 'text-success' }} mb-0">
                                        Rp {{ number_format($unallocatedAmount, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <span class="badge bg-primary-transparent text-primary">Status Billing: {{ $billing['status'] ?? '-' }}</span>
                            @if($canManageBilling)
                                <div class="d-flex flex-wrap gap-2">
                                    @if($canAddBillingDetail)
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal_add_billing_detail">
                                            <i class="ri-add-circle-line me-1"></i> Tambah Billing Detail
                                        </button>
                                    @endif
                                    @if(!$dpVerified)
                                        <span class="badge bg-warning-transparent text-warning py-2 px-3">
                                            <i class="ri-lock-line me-1"></i> Generate tagihan tersedia setelah DP verified
                                        </span>
                                    @else
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal_generate_installment"
                                            @if(!$canGenerateInstallment) disabled @endif>
                                            <i class="ri-file-list-3-line me-1"></i> Generate Tagihan
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if($unallocatedAmount > 0)
                            <div class="alert alert-warning py-2 mb-3">
                                Ada nominal <strong>Rp {{ number_format($unallocatedAmount, 0, ',', '.') }}</strong> yang belum dijadwalkan ke tagihan customer.
                            </div>
                        @endif

                        <ul class="nav nav-pills billing-subtabs mb-3" id="billing_sub_tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="billing_overview_tab" data-bs-toggle="pill" data-bs-target="#billing_overview_pane" type="button" role="tab" aria-controls="billing_overview_pane" aria-selected="true">
                                    Billing Summary
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="billing_details_tab" data-bs-toggle="pill" data-bs-target="#billing_details_pane" type="button" role="tab" aria-controls="billing_details_pane" aria-selected="false">
                                    Billing Details
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="billing_installments_tab" data-bs-toggle="pill" data-bs-target="#billing_installments_pane" type="button" role="tab" aria-controls="billing_installments_pane" aria-selected="false">
                                    Billing Installments
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="billing_sub_tabs_content">
                            <div class="tab-pane fade show active" id="billing_overview_pane" role="tabpanel" aria-labelledby="billing_overview_tab">
                                <div class="table-responsive">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted ps-0" style="width: 220px;">Total Billing</td>
                                                <td class="fw-semibold">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted ps-0">Total Komponen Biaya</td>
                                                <td>Rp {{ number_format($totalDetailAmount, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted ps-0">Total Tagihan Terjadwal</td>
                                                <td>Rp {{ number_format($totalInstallmentAmount, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted ps-0">Sisa Belum Dijadwalkan</td>
                                                <td class="{{ $unallocatedAmount > 0 ? 'text-warning' : 'text-success' }} fw-semibold">
                                                    Rp {{ number_format($unallocatedAmount, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted ps-0">Total Dibayar</td>
                                                <td class="text-success fw-semibold">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted ps-0">Sisa Pembayaran</td>
                                                <td class="text-danger fw-semibold">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted ps-0">Aturan DP</td>
                                                <td>{{ $dpPercentage ?? 15 }}% (batas bayar H+{{ $dpDueDays ?? 3 }})</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="billing_details_pane" role="tabpanel" aria-labelledby="billing_details_tab">
                                @if(empty($billing['details']))
                                    <p class="text-muted mb-0">Belum ada komponen billing.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Komponen Biaya</th>
                                                    <th>Tipe</th>
                                                    <th>Catatan</th>
                                                    <th class="text-end">Nominal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($billing['details'] as $detail)
                                                    <tr>
                                                        <td class="fw-semibold">{{ $detail['name'] }}</td>
                                                        <td>
                                                            @php
                                                                $isAddonType = strtoupper((string) ($detail['type_code'] ?? '')) === 'BLT_ADDON';
                                                            @endphp
                                                            <span class="badge {{ $isAddonType ? 'bg-warning-transparent text-warning' : 'bg-primary-transparent text-primary' }}">
                                                                {{ $detail['type'] ?: ($isAddonType ? 'ADDON' : 'BASE') }}
                                                            </span>
                                                        </td>
                                                        <td class="text-muted small">{{ $detail['description'] !== '' ? $detail['description'] : '-' }}</td>
                                                        <td class="text-end fw-semibold">Rp {{ number_format((float) ($detail['amount'] ?? 0), 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            <div class="tab-pane fade" id="billing_installments_pane" role="tabpanel" aria-labelledby="billing_installments_tab">
                                @if(empty($billing['installments']))
                                    <p class="text-muted mb-0">Belum ada tagihan installment.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Jatuh Tempo</th>
                                                    <th>Tipe</th>
                                                    <th class="text-end">Tagihan</th>
                                                    <th class="text-end">Dibayar</th>
                                                    <th class="text-end">Sisa</th>
                                                    <th>Status</th>
                                                    <th>Pembayaran</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($billing['installments'] as $installment)
                                                    <tr>
                                                        <td>{{ $installment['due_date'] }}</td>
                                                        <td><span class="badge bg-primary-transparent text-primary">{{ $installment['type'] }}</span></td>
                                                        <td class="text-end fw-semibold">Rp {{ number_format((float) ($installment['amount'] ?? 0), 0, ',', '.') }}</td>
                                                        <td class="text-end text-success">Rp {{ number_format((float) ($installment['paid_amount'] ?? 0), 0, ',', '.') }}</td>
                                                        <td class="text-end text-danger">Rp {{ number_format((float) ($installment['remaining_amount'] ?? 0), 0, ',', '.') }}</td>
                                                        <td><span class="badge bg-info-transparent text-info">{{ $installment['status'] }}</span></td>
                                                        <td>
                                                            @php
                                                                $paymentCount = is_array($installment['payments'] ?? null) ? count($installment['payments']) : 0;
                                                            @endphp
                                                            @if($paymentCount > 0)
                                                                <button class="btn btn-link btn-sm p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#installment_payments_{{ $installment['id'] }}" aria-expanded="false" aria-controls="installment_payments_{{ $installment['id'] }}">
                                                                    {{ $paymentCount }} pembayaran
                                                                </button>
                                                            @else
                                                                <span class="text-muted small">Belum ada</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @if($paymentCount > 0)
                                                        <tr class="collapse" id="installment_payments_{{ $installment['id'] }}">
                                                            <td colspan="7" class="bg-light">
                                                                <div class="ps-2 py-1">
                                                                    <p class="mb-2 fw-semibold small">Riwayat Pembayaran</p>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-borderless table-sm mb-0">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th class="text-muted">Tanggal Bayar</th>
                                                                                        <th class="text-muted">Metode</th>
                                                                                        <th class="text-muted">Status</th>
                                                                                        <th class="text-muted text-end">Nominal</th>
                                                                                        <th class="text-muted">Bukti</th>
                                                                                        <th class="text-muted">Aksi</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    @foreach($installment['payments'] as $payment)
                                                                                        @php
                                                                                            $isPending = !empty($payment['is_pending']);
                                                                                            $isCustomer = !empty($payment['is_customer_upload']);
                                                                                            $rowClass = $isPending ? 'table-warning' : '';
                                                                                            $hasReceipt = !empty($payment['has_receipt']) && !empty($payment['receipt_preview_url']);
                                                                                        @endphp
                                                                                        <tr class="{{ $rowClass }}">
                                                                                            <td>{{ $payment['paid_at'] }}</td>
                                                                                            <td>{{ $payment['method'] }}</td>
                                                                                            <td>
                                                                                                @if($isPending)
                                                                                                    <span class="badge bg-warning text-dark">
                                                                                                        <i class="ri-time-line"></i> Pending
                                                                                                        @if($isCustomer)
                                                                                                            <small>(Customer)</small>
                                                                                                        @endif
                                                                                                    </span>
                                                                                                @elseif(($payment['status_code'] ?? '') === 'PYS_SUCCESS')
                                                                                                    <span class="badge bg-success-transparent text-success">
                                                                                                        <i class="ri-check-line"></i> {{ $payment['status'] }}
                                                                                                    </span>
                                                                                                @elseif(($payment['status_code'] ?? '') === 'PYS_FAILED')
                                                                                                    <span class="badge bg-danger-transparent text-danger">
                                                                                                        <i class="ri-close-line"></i> {{ $payment['status'] }}
                                                                                                    </span>
                                                                                                @else
                                                                                                    <span class="badge bg-light text-muted">{{ $payment['status'] }}</span>
                                                                                                @endif
                                                                                            </td>
                                                                                            <td class="text-end">Rp {{ number_format((float) ($payment['amount'] ?? 0), 0, ',', '.') }}</td>
                                                                                            <td>
                                                                                                @if($hasReceipt)
                                                                                                    @if($isPending)
                                                                                                        <a href="{{ $payment['receipt_preview_url'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info py-0 px-2">
                                                                                                            <i class="ri-eye-line"></i> Lihat Bukti
                                                                                                        </a>
                                                                                                        <br><small class="text-muted">{{ $payment['receipt_name'] ?? '' }}</small>
                                                                                                    @else
                                                                                                        <a href="{{ $payment['receipt_preview_url'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0 px-2">
                                                                                                            <i class="ri-attachment-line"></i> Lihat
                                                                                                        </a>
                                                                                                    @endif
                                                                                                @else
                                                                                                    @if($isPending && $isCustomer)
                                                                                                        <span class="badge bg-secondary text-white"><i class="ri-image-off-line"></i> Tanpa Bukti</span>
                                                                                                    @else
                                                                                                        <span class="text-muted small">-</span>
                                                                                                    @endif
                                                                                                @endif
                                                                                            </td>
                                                                                            <td>
                                                                                                @if($isPending)
                                                                                                    <div class="d-flex gap-1">
                                                                                                        <button type="button" class="btn btn-sm btn-success py-0 px-2 btn-approve-payment" data-payment-id="{{ $payment['id'] }}" data-booking-id="{{ $booking->id }}" data-url="{{ route('api.admin.bookings.payments.approve', [$booking->id, $payment['id']]) }}" title="Approve bukti pembayaran">
                                                                                                            <i class="ri-check-line"></i>
                                                                                                        </button>
                                                                                                        <button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-reject-payment" data-payment-id="{{ $payment['id'] }}" data-booking-id="{{ $booking->id }}" data-url="{{ route('api.admin.bookings.payments.reject', [$booking->id, $payment['id']]) }}" title="Tolak bukti pembayaran">
                                                                                                            <i class="ri-close-line"></i>
                                                                                                        </button>
                                                                                                    </div>
                                                                                                @else
                                                                                                    <span class="text-muted small">-</span>
                                                                                                @endif
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">Billing akan otomatis tersedia setelah booking masuk status <strong>Approved - Waiting DP</strong>.</p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card custom-card mb-3">
            <div class="card-header bg-primary-transparent">
                <h5 class="card-title mb-0 text-primary"><i class="ri-action me-1"></i> Aksi</h5>
            </div>
            <div class="card-body">
                @if(empty($availableActions))
                    <p class="text-muted mb-0">Tidak ada aksi yang tersedia untuk status booking saat ini.</p>
                @else
                    <div class="d-grid gap-2">
                        @if(in_array('approve', $availableActions))
                            <button type="button" class="btn btn-success" id="btn_approve_booking" data-booking-id="{{ $booking->id }}" data-url="{{ route('api.admin.bookings.approve', $booking->id) }}">
                                <i class="ri-check-double-line me-1"></i> Approve Request Booking
                            </button>
                        @endif
                        @if(in_array('reject', $availableActions))
                            <button type="button" class="btn btn-danger" id="btn_reject_booking" data-booking-id="{{ $booking->id }}" data-url="{{ route('api.admin.bookings.reject', $booking->id) }}">
                                <i class="ri-close-circle-line me-1"></i> Reject Request Booking
                            </button>
                        @endif
                        @if(in_array('upload_dp', $availableActions))
                            <button type="button" class="btn btn-primary" id="btn_upload_dp" data-bs-toggle="modal" data-bs-target="#modal_upload_dp">
                                <i class="ri-upload-line me-1"></i> Upload Manual DP
                            </button>
                        @endif
                        @if(in_array('verify_dp', $availableActions))
                            <button type="button" class="btn btn-success" id="btn_verify_dp" data-booking-id="{{ $booking->id }}" data-url="{{ route('api.admin.bookings.verify-dp', $booking->id) }}">
                                <i class="ri-verified-badge-line me-1"></i> Verifikasi DP
                            </button>
                        @endif
                        @if(in_array('reject_manual', $availableActions))
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal_reject_manual">
                                <i class="ri-close-circle-line me-1"></i> Reject Booking
                            </button>
                        @endif
                        @if(in_array('upload_final', $availableActions))
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_upload_final">
                                <i class="ri-upload-line me-1"></i> Upload Manual Pembayaran
                            </button>
                        @endif
                        @if(in_array('verify_final', $availableActions))
                            <button type="button" class="btn btn-success" id="btn_verify_final" data-booking-id="{{ $booking->id }}" data-url="{{ route('api.admin.bookings.verify-final', $booking->id) }}">
                                <i class="ri-verified-badge-line me-1"></i> Verifikasi Pelunasan
                            </button>
                        @endif
                        @if(in_array('cancel_booking', $availableActions))
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal_cancel_booking">
                                <i class="ri-close-line me-1"></i> Batalkan Booking
                            </button>
                        @endif
                        @if(in_array('complete_booking', $availableActions))
                            <button type="button" class="btn btn-success" id="btn_complete_booking" data-booking-id="{{ $booking->id }}" data-url="{{ route('api.admin.bookings.complete', $booking->id) }}">
                                <i class="ri-check-line me-1"></i> Selesaikan Booking
                            </button>
                        @endif
                        @if(in_array('force_majeure', $availableActions))
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal_force_majeure">
                                <i class="ri-alert-line me-1"></i> Force Majeure
                            </button>
                        @endif
                        @if(in_array('upload_refund_proof', $availableActions))
                            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modal_upload_refund_proof">
                                <i class="ri-refund-line me-1"></i> Upload Bukti Refund
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="card custom-card mb-3">
            <div class="card-header pb-0">
                <h5 class="card-title mb-0">History Status Booking</h5>
            </div>
            <div class="card-body tab-content pt-3" id="booking_detail_side_tabs_content">
                <div class="tab-pane fade show active" id="pane_history_status" role="tabpanel" aria-labelledby="tab_history_status">
                    @if(empty($timeline))
                        <p class="text-muted mb-0">Belum ada riwayat status.</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach($timeline as $index => $item)
                                @php
                                    $isLast = $index === array_key_last($timeline);
                                    $badgeClass = match($item['code']) {
                                        'BS_WAITING_APPROVAL' => 'bg-warning-transparent text-warning',
                                        'BS_APPROVED_WAITING_DP' => 'bg-info-transparent text-info',
                                        'BS_APPROVED_WAITING_FINAL_PAYMENT' => 'bg-info-transparent text-info',
                                        'BS_CONFIRMED', 'BS_COMPLETE' => 'bg-success-transparent text-success',
                                        'BS_CANCEL', 'BS_EXPIRED', 'BS_EXPIRED_DP', 'BS_REFUND', 'BS_REJECTED' => 'bg-danger-transparent text-danger',
                                        'BS_FORCE_MAJEURE', 'BS_RESCHEDULE' => 'bg-warning-transparent text-warning',
                                        default => 'bg-light text-muted',
                                    };
                                @endphp
                                <li class="d-flex align-items-start gap-3 {{ !$isLast ? 'mb-3 pb-3 border-bottom' : '' }}">
                                    <div class="avatar avatar-sm {{ $badgeClass }} rounded">
                                        <span class="avatar-title fs-6"><i class="ri-checkbox-blank-circle-fill"></i></span>
                                    </div>
                                    <div class="flex-fill">
                                        <p class="mb-1 fw-semibold">{{ $item['label'] }}</p>
                                        <p class="mb-1 text-muted small">{{ $item['description'] }}</p>
                                        <small class="text-muted">Oleh: {{ $item['operator'] }} &middot; {{ $item['date'] }}</small>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

@if($billing && $canManageBilling)
<div class="modal fade" id="modal_add_billing_detail" tabindex="-1" aria-labelledby="modal_add_billing_detail_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_add_billing_detail_label">Tambah Billing Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="form_add_billing_detail" data-url="{{ route('api.admin.bookings.billing-details.store', $booking->id) }}">
                <div class="modal-body">
                    <p class="text-muted small mb-3">Gunakan untuk menambahkan komponen biaya baru seperti transportasi, overtime, atau add-on lainnya.</p>
                    <input type="hidden" name="billing_type_code" value="BLT_ADDON">
                    <div class="alert alert-info py-2 px-3 mb-3">
                        Komponen ini akan dicatat sebagai <strong>Add-on</strong> di luar paket dasar.
                    </div>
                    <div class="mb-2">
                        <label for="billing_detail_name" class="form-label">Nama Komponen</label>
                        <input type="text" id="billing_detail_name" name="name" class="form-control" maxlength="160" placeholder="Contoh: Add-on transportasi luar kota" required>
                    </div>
                    <div class="mb-2">
                        <label for="billing_detail_amount" class="form-label">Nominal</label>
                        <input type="number" id="billing_detail_amount" name="amount" min="1" step="any" class="form-control" placeholder="Contoh: 500000" required>
                    </div>
                    <div class="mb-0">
                        <label for="billing_detail_description" class="form-label">Catatan (opsional)</label>
                        <textarea id="billing_detail_description" name="description" class="form-control" rows="3" maxlength="600" placeholder="Detail tambahan untuk tim internal dan customer."></textarea>
                    </div>
                    <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="billing_detail_error_box"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_add_billing_detail">
                        <i class="ri-save-line me-1"></i> Simpan Komponen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_generate_installment" tabindex="-1" aria-labelledby="modal_generate_installment_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_generate_installment_label">Generate Tagihan Installment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="form_generate_installment" data-url="{{ route('api.admin.bookings.installments.store', $booking->id) }}">
                <div class="modal-body">
                    <div class="alert alert-warning py-2 px-3 mb-3">
                        Sisa nominal yang belum dijadwalkan: <strong>{{ $billingTools['unallocated_label'] ?? 'Rp 0' }}</strong>
                    </div>
                    <div class="mb-2">
                        <label for="installment_type_code" class="form-label">Tipe Tagihan</label>
                        <select id="installment_type_code" name="installment_type_code" class="form-select">
                            @foreach($installmentTypeOptions as $type)
                                <option value="{{ $type['code'] ?? '' }}">{{ $type['label'] ?? '-' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="installment_amount" class="form-label">Nominal Tagihan</label>
                        <input type="number" id="installment_amount" name="amount" min="1" step="any" class="form-control" placeholder="Contoh: 1500000" value="{{ (float) ($billingTools['unallocated_amount'] ?? 0) > 0 ? (int) round((float) ($billingTools['unallocated_amount'] ?? 0)) : '' }}" required>
                    </div>
                    <div class="mb-0">
                        <label for="installment_due_date" class="form-label">Jatuh Tempo</label>
                        <input type="date" id="installment_due_date" name="due_date" class="form-control" value="{{ $defaultInstallmentDueDate }}" required>
                    </div>
                    <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="installment_error_box"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btn_submit_generate_installment">
                        <i class="ri-file-list-3-line me-1"></i> Buat Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(in_array('upload_dp', $availableActions) || in_array('upload_final', $availableActions))
<div class="modal fade" id="modal_upload_dp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Manual DP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="form_upload_dp" data-url="{{ route('api.admin.bookings.upload-payment', $booking->id) }}" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 mb-3">
                        DP harus dibayar <strong>penuh</strong>. Nominal pembayaran akan otomatis mengikuti sisa tagihan DP.
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Installment DP</label>
                        <select name="billing_installment_id" class="form-select" required>
                            @foreach($dpInstallments as $inst)
                                <option value="{{ $inst['id'] }}" data-amount="{{ (float) ($inst['remaining_amount'] ?? 0) }}">{{ $inst['type'] }} - Rp {{ number_format((float) ($inst['amount'] ?? 0), 0, ',', '.') }} (Sisa: Rp {{ number_format((float) ($inst['remaining_amount'] ?? 0), 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nominal yang Akan Dibayar</label>
                        <input type="text" class="form-control" id="upload_dp_display_amount" readonly value="@if(!empty($dpInstallments)) Rp {{ number_format((float) ($dpInstallments[0]['remaining_amount'] ?? 0), 0, ',', '.') }} @endif">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="payment_method_code" class="form-select">
                            <option value="PYM_BANK_TRANSFER">Bank Transfer</option>
                            <option value="PYM_QRIS">QRIS</option>
                            <option value="PYM_CASH">Cash</option>
                            <option value="PYM_E_WALLET">E-Wallet</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Bukti Transfer (opsional)</label>
                        <input type="file" name="transfer_receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.webp">
                        <div class="form-text">Format: JPG, PNG, WebP, PDF. Maks 10MB.</div>
                    </div>
                    <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="upload_dp_error_box"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_upload_dp">
                        <i class="ri-save-line me-1"></i> Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_upload_final" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Manual Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="form_upload_final" data-url="{{ route('api.admin.bookings.upload-payment', $booking->id) }}" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Installment</label>
                        <select name="billing_installment_id" class="form-select" id="final_installment_select" required>
                            @foreach($nonDpInstallments as $inst)
                                <option value="{{ $inst['id'] }}" data-amount="{{ (float) ($inst['remaining_amount'] ?? 0) }}">{{ $inst['type'] }} - Rp {{ number_format((float) ($inst['amount'] ?? 0), 0, ',', '.') }} (Sisa: Rp {{ number_format((float) ($inst['remaining_amount'] ?? 0), 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="final_partial_check" name="is_partial" value="true">
                            <label class="form-check-label" for="final_partial_check">Bayar parsial (sebagian)</label>
                        </div>
                    </div>
                    <div class="mb-2" id="final_full_amount_wrap">
                        <label class="form-label">Nominal yang Akan Dibayar</label>
                        <input type="text" class="form-control" id="final_display_amount" readonly value="@if(!empty($nonDpInstallments)) Rp {{ number_format((float) ($nonDpInstallments[0]['remaining_amount'] ?? 0), 0, ',', '.') }} @endif">
                    </div>
                    <div class="mb-2 d-none" id="final_partial_amount_wrap">
                        <label class="form-label">Nominal Pembayaran</label>
                        <input type="number" name="amount" id="final_partial_amount_input" min="1" step="any" class="form-control" placeholder="Contoh: 5000000">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="payment_method_code" class="form-select">
                            <option value="PYM_BANK_TRANSFER">Bank Transfer</option>
                            <option value="PYM_QRIS">QRIS</option>
                            <option value="PYM_CASH">Cash</option>
                            <option value="PYM_E_WALLET">E-Wallet</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Bukti Transfer (opsional)</label>
                        <input type="file" name="transfer_receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.webp">
                        <div class="form-text">Format: JPG, PNG, WebP, PDF. Maks 10MB.</div>
                    </div>
                    <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="upload_final_error_box"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_upload_final">
                        <i class="ri-save-line me-1"></i> Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(in_array('reject_manual', $availableActions))
<div class="modal fade" id="modal_reject_manual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="form_reject_manual" data-url="{{ route('api.admin.bookings.reject-manual', $booking->id) }}">
                <div class="modal-body">
                    <div class="alert alert-warning py-2 px-3 mb-3">
                        Aksi ini akan menolak booking dan membatalkan billing. Aksi tidak bisa dibatalkan.
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Alasan Reject (opsional)</label>
                        <textarea name="reason" class="form-control" rows="3" maxlength="600" placeholder="Alasan penolakan booking..."></textarea>
                    </div>
                    <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="reject_manual_error_box"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="btn_submit_reject_manual">
                        <i class="ri-close-circle-line me-1"></i> Reject Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(in_array('cancel_booking', $availableActions))
<div class="modal fade" id="modal_cancel_booking" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Batalkan Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="form_cancel_booking" data-url="{{ route('api.admin.bookings.cancel', $booking->id) }}">
                <div class="modal-body">
                    <div class="alert alert-danger py-2 px-3 mb-3">
                        Pembatalan booking akan membatalkan billing. DP yang sudah dibayarkan akan hangus (non-refundable).
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Alasan Pembatalan (opsional)</label>
                        <textarea name="reason" class="form-control" rows="3" maxlength="600" placeholder="Alasan pembatalan booking..."></textarea>
                    </div>
                    <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="cancel_booking_error_box"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="btn_submit_cancel_booking">
                        <i class="ri-close-line me-1"></i> Batalkan Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(in_array('force_majeure', $availableActions))
<div class="modal fade" id="modal_force_majeure" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Force Majeure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="form_force_majeure" data-url="{{ route('api.admin.bookings.force-majeure', $booking->id) }}">
                <div class="modal-body">
                    <div class="alert alert-warning py-2 px-3 mb-3">
                        Force majeure akan mengubah status booking. Pilih opsi penanganan yang sesuai.
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Alasan Force Majeure</label>
                        <textarea name="reason" class="form-control" rows="3" maxlength="600" placeholder="Jelaskan alasan force majeure..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Opsi Penanganan</label>
                        <select name="action" class="form-select" id="fm_action_select">
                            <option value="reschedule">Ganti Tanggal (Reschedule)</option>
                            <option value="refund">Refund</option>
                        </select>
                    </div>
                    <div class="mb-0" id="fm_new_date_wrap">
                        <label class="form-label">Tanggal Baru</label>
                        <input type="date" name="new_date" class="form-control" id="fm_new_date" value="{{ $booking->event_date?->format('Y-m-d') ?? '' }}">
                    </div>
                    <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="force_majeure_error_box"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btn_submit_force_majeure">
                        <i class="ri-alert-line me-1"></i> Proses Force Majeure
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@php
    $refundInstallments = collect($billing['installments'] ?? [])->filter(function ($i) {
        return strtoupper(trim((string) ($i['type_code'] ?? ''))) === 'INS_REFUND';
    });
    $refundRemaining = $refundInstallments->sum(function ($i) { return (float) ($i['remaining_amount'] ?? 0); });
@endphp

@if(in_array('upload_refund_proof', $availableActions))
<div class="modal fade" id="modal_upload_refund_proof" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Bukti Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="form_upload_refund_proof" data-url="{{ route('api.admin.bookings.upload-refund-proof', $booking->id) }}" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 mb-3">
                        Nominal refund otomatis mengikuti sisa tagihan refund: <strong>Rp {{ number_format($refundRemaining, 0, ',', '.') }}</strong>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="payment_method_code" class="form-select">
                            <option value="PYM_BANK_TRANSFER">Bank Transfer</option>
                            <option value="PYM_QRIS">QRIS</option>
                            <option value="PYM_CASH">Cash</option>
                            <option value="PYM_E_WALLET">E-Wallet</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Bukti Transfer (opsional)</label>
                        <input type="file" name="transfer_receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.webp">
                        <div class="form-text">Format: JPG, PNG, WebP, PDF. Maks 10MB.</div>
                    </div>
                    <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="upload_refund_error_box"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info" id="btn_submit_upload_refund">
                        <i class="ri-save-line me-1"></i> Simpan Bukti Refund
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endif
@endsection

@push('styles')
<style>
    .booking-detail-main-tabs-wrap{
        overflow-x:auto;
    }
    .booking-detail-main-tabs{
        min-width:max-content;
        border-bottom:0;
        gap:.25rem;
        padding-bottom:.35rem;
    }
    .booking-detail-main-tabs .nav-link{
        border-radius:.5rem .5rem 0 0;
        white-space:nowrap;
        font-weight:500;
    }
    .booking-detail-main-tabs .nav-link.active{
        border-color:transparent;
        background:rgba(var(--primary-rgb),.12);
        color:var(--primary-color);
    }
    .booking-detail-side-tabs{
        gap:.4rem;
    }
    .booking-detail-preline{
        white-space:pre-line;
    }
    .billing-subtabs{
        gap:.35rem;
    }
    .billing-subtabs .nav-link{
        padding:.4rem .75rem;
        border-radius:999px;
        font-size:.8rem;
    }
    .billing-subtabs .nav-link.active{
        background:rgba(var(--primary-rgb),.12);
        color:var(--primary-color);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var approveBtn = document.getElementById('btn_approve_booking');
    var rejectBtn = document.getElementById('btn_reject_booking');
    var addBillingDetailForm = document.getElementById('form_add_billing_detail');
    var generateInstallmentForm = document.getElementById('form_generate_installment');
    var uploadDpForm = document.getElementById('form_upload_dp');
    var uploadFinalForm = document.getElementById('form_upload_final');
    var rejectManualForm = document.getElementById('form_reject_manual');
    var cancelBookingForm = document.getElementById('form_cancel_booking');
    var forceMajeureForm = document.getElementById('form_force_majeure');
    var uploadRefundForm = document.getElementById('form_upload_refund_proof');

    function handleAction(btn, confirmMsg) {
        if (!btn) return;
        btn.addEventListener('click', function() {
            if (!confirm(confirmMsg)) return;
            var url = btn.dataset.url;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                alert(data.message || 'Berhasil');
                window.location.reload();
            })
            .catch(function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
                btn.disabled = false;
            });
        });
    }

    function safeParseJson(res) {
        return res.text().then(function(text) {
            if (!text) return {};
            try {
                return JSON.parse(text);
            } catch (err) {
                return {};
            }
        });
    }

    function bindModalFormSubmit(form, submitBtnId, errorBoxId, successMessage) {
        if (!form) return;
        var submitBtn = document.getElementById(submitBtnId);
        var errorBox = document.getElementById(errorBoxId);
        var initialBtnHtml = submitBtn ? submitBtn.innerHTML : '';
        var hasFile = form.enctype === 'multipart/form-data';

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            if (!submitBtn) return;

            if (errorBox) {
                errorBox.classList.add('d-none');
                errorBox.textContent = '';
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

            var fetchOptions = {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            if (hasFile) {
                var formData = new FormData(form);
                fetchOptions.body = formData;
            } else {
                var payload = {};
                var plainFormData = new FormData(form);
                plainFormData.forEach(function(value, key) {
                    payload[key] = typeof value === 'string' ? value.trim() : value;
                });
                fetchOptions.headers['Content-Type'] = 'application/json';
                fetchOptions.body = JSON.stringify(payload);
            }

            fetch(form.dataset.url, fetchOptions)
            .then(function(res) {
                return safeParseJson(res).then(function(data) {
                    if (!res.ok) {
                        var errMessage = (data && data.message) ? data.message : 'Terjadi kesalahan. Silakan cek data Anda.';
                        throw new Error(errMessage);
                    }
                    return data;
                });
            })
            .then(function(data) {
                alert(data.message || successMessage);
                window.location.reload();
            })
            .catch(function(error) {
                if (errorBox) {
                    errorBox.textContent = error.message || 'Terjadi kesalahan. Silakan coba lagi.';
                    errorBox.classList.remove('d-none');
                } else {
                    alert(error.message || 'Terjadi kesalahan. Silakan coba lagi.');
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = initialBtnHtml;
            });
        });
    }

    handleAction(approveBtn, 'Approve booking ini? Billing DP akan otomatis dibuat.');
    handleAction(rejectBtn, 'Reject booking ini? Aksi ini tidak bisa dibatalkan.');
    bindModalFormSubmit(addBillingDetailForm, 'btn_submit_add_billing_detail', 'billing_detail_error_box', 'Komponen billing berhasil disimpan.');
    bindModalFormSubmit(generateInstallmentForm, 'btn_submit_generate_installment', 'installment_error_box', 'Tagihan installment berhasil dibuat.');
    bindModalFormSubmit(uploadDpForm, 'btn_submit_upload_dp', 'upload_dp_error_box', 'Pembayaran DP berhasil dicatat.');
    bindModalFormSubmit(uploadFinalForm, 'btn_submit_upload_final', 'upload_final_error_box', 'Pembayaran berhasil dicatat.');
    bindModalFormSubmit(rejectManualForm, 'btn_submit_reject_manual', 'reject_manual_error_box', 'Booking berhasil ditolak.');
    bindModalFormSubmit(cancelBookingForm, 'btn_submit_cancel_booking', 'cancel_booking_error_box', 'Booking berhasil dibatalkan.');
    bindModalFormSubmit(forceMajeureForm, 'btn_submit_force_majeure', 'force_majeure_error_box', 'Force majeure berhasil diproses.');
    bindModalFormSubmit(uploadRefundForm, 'btn_submit_upload_refund', 'upload_refund_error_box', 'Bukti refund berhasil disimpan.');

    var verifyDpBtn = document.getElementById('btn_verify_dp');
    var verifyFinalBtn = document.getElementById('btn_verify_final');
    var completeBtn = document.getElementById('btn_complete_booking');
    handleAction(verifyDpBtn, 'Verifikasi DP? Pastikan pembayaran DP sudah diterima. Status akan berubah ke Waiting Final Payment.');
    handleAction(verifyFinalBtn, 'Verifikasi pelunasan? Pastikan semua pembayaran sudah diterima. Status akan berubah ke Confirmed.');
    handleAction(completeBtn, 'Selesaikan booking? Ini menandakan acara sudah dilaksanakan.');

    document.querySelectorAll('.btn-approve-payment').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Approve bukti pembayaran ini? Nominal akan dicatat sebagai pembayaran valid.')) return;
            var url = btn.dataset.url;
            btn.disabled = true;
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(res) {
                return res.json().then(function(data) {
                    if (!res.ok) throw new Error(data.message || 'Gagal approve.');
                    return data;
                });
            })
            .then(function() { window.location.reload(); })
            .catch(function(err) { alert(err.message); btn.disabled = false; });
        });
    });

    document.querySelectorAll('.btn-reject-payment').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var reason = prompt('Alasan penolakan (opsional):');
            var url = btn.dataset.url;
            btn.disabled = true;
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ reason: reason || '' })
            })
            .then(function(res) {
                return res.json().then(function(data) {
                    if (!res.ok) throw new Error(data.message || 'Gagal reject.');
                    return data;
                });
            })
            .then(function() { window.location.reload(); })
            .catch(function(err) { alert(err.message); btn.disabled = false; });
        });
    });

    var fmActionSelect = document.getElementById('fm_action_select');
    var fmNewDateWrap = document.getElementById('fm_new_date_wrap');
    var fmNewDateInput = document.getElementById('fm_new_date');
    if (fmActionSelect && fmNewDateWrap) {
        function toggleFmNewDate() {
            var isReschedule = fmActionSelect.value === 'reschedule';
            fmNewDateWrap.style.display = isReschedule ? '' : 'none';
            if (fmNewDateInput) fmNewDateInput.required = isReschedule;
        }
        fmActionSelect.addEventListener('change', toggleFmNewDate);
        toggleFmNewDate();
    }

    var dpInstallmentSelect = document.querySelector('#form_upload_dp select[name="billing_installment_id"]');
    var dpDisplayAmount = document.getElementById('upload_dp_display_amount');
    if (dpInstallmentSelect && dpDisplayAmount) {
        dpInstallmentSelect.addEventListener('change', function() {
            var opt = dpInstallmentSelect.options[dpInstallmentSelect.selectedIndex];
            var amt = parseFloat(opt.dataset.amount || 0);
            dpDisplayAmount.value = 'Rp ' + amt.toLocaleString('id-ID');
        });
    }

    var finalPartialCheck = document.getElementById('final_partial_check');
    var finalFullWrap = document.getElementById('final_full_amount_wrap');
    var finalPartialWrap = document.getElementById('final_partial_amount_wrap');
    var finalDisplayAmount = document.getElementById('final_display_amount');
    var finalPartialInput = document.getElementById('final_partial_amount_input');
    var finalInstallmentSelect = document.getElementById('final_installment_select');

    if (finalPartialCheck && finalFullWrap && finalPartialWrap) {
        function toggleFinalPartial() {
            var isPartial = finalPartialCheck.checked;
            finalFullWrap.style.display = isPartial ? 'none' : '';
            finalPartialWrap.style.display = isPartial ? '' : 'none';
            if (isPartial && finalPartialInput) {
                finalPartialInput.required = true;
            } else if (finalPartialInput) {
                finalPartialInput.required = false;
                finalPartialInput.value = '';
            }
        }
        finalPartialCheck.addEventListener('change', toggleFinalPartial);
        toggleFinalPartial();
    }

    if (finalInstallmentSelect && finalDisplayAmount) {
        finalInstallmentSelect.addEventListener('change', function() {
            var opt = finalInstallmentSelect.options[finalInstallmentSelect.selectedIndex];
            var amt = parseFloat(opt.dataset.amount || 0);
            finalDisplayAmount.value = 'Rp ' + amt.toLocaleString('id-ID');
        });
    }
});
</script>
@endpush
