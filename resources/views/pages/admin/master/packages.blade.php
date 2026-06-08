@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@php
    $actions = [
        ['label' => 'Daftar Booking', 'url' => panel_route('admin.bookings.list'), 'class' => 'btn btn-primary btn-sm'],
    ];

    $alerts = [
        ['class' => 'alert-info', 'text' => 'Base price boleh ditampilkan di awal. Harga final baru muncul setelah DP verified dan pengecekan lokasi selesai.'],
    ];

    $columns = ['Paket', 'Harga Dasar', 'Durasi', 'DP Wedding', 'DP Non-wedding', 'Keterangan', 'Status'];
    $rows = [
        ['Intim', 'IDR 6.000.000', '2 jam', '15%', '10%', 'Cocok untuk acara ringkas', ['type' => 'badge', 'tone' => 'success', 'label' => 'active']],
        ['Andalan', 'IDR 12.000.000', '6 jam', '15%', '10%', 'Pilihan paling stabil untuk wedding', ['type' => 'badge', 'tone' => 'success', 'label' => 'active']],
        ['Mewah', 'IDR 20.000.000', '2 hari', '15%', '10%', 'Produksi premium multi sesi', ['type' => 'badge', 'tone' => 'success', 'label' => 'active']],
    ];

    $sideCards = [
        [
            'title' => 'Ketentuan Paket',
            'items' => [
                ['label' => 'Skema DP Wedding', 'value' => '15%'],
                ['label' => 'Skema DP Non-wedding', 'value' => '10%'],
                ['label' => 'Pelunasan', 'value' => 'Maksimal H-1 acara'],
                ['label' => 'Payment Method', 'value' => 'Manual transfer'],
            ],
        ],
        [
            'title' => 'Pengingat Copy Public',
            'bullets' => [
                'Tampilkan base price + estimasi tambahan lokasi.',
                'Jangan tampilkan angka final di tahap awal.',
                'Sertakan note transparansi biaya tambahan.',
            ],
        ],
    ];
@endphp

@include('pages.admin.partials.page-header', [
    'heading' => 'Packages',
    'summary' => 'Master data paket untuk menjaga konsistensi base price, durasi layanan, dan skema DP.',
    'actions' => $actions,
])

@include('pages.admin.partials.alerts', ['alerts' => $alerts])

<div class="row g-3">
    <div class="col-12 col-xl-8">
        @include('pages.admin.partials.data-table', [
            'tableTitle' => 'Master Paket',
            'tableBadge' => 'Base Price',
            'columns' => $columns,
            'rows' => $rows,
        ])
    </div>
    <div class="col-12 col-xl-4">
        @include('pages.admin.partials.side-cards', ['cards' => $sideCards])
    </div>
</div>
@endsection

