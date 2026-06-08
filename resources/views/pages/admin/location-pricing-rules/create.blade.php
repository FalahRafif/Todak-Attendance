@extends('layouts.admin.admin')

@section('title', $title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/pages/admin/location-pricing-rules/form.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@section('content')
@include('pages.admin.partials.page-header', [
    'heading' => 'Tambah Aturan Harga Lokasi',
    'summary' => 'Tambahkan aturan harga berdasarkan lokasi yang dipilih (provinsi, kota/kabupaten, kecamatan, atau kelurahan).',
    'actions' => [
        ['label' => 'Kembali ke Daftar', 'url' => route('admin.location.rules'), 'class' => 'btn btn-outline-primary btn-sm'],
    ],
])

@include('pages.admin.location-pricing-rules._form', [
    'formTitle' => 'Form Tambah Aturan',
    'formAction' => route('api.admin.location-rules.store'),
    'submitLabel' => 'Simpan Aturan',
    'locationLevels' => $locationLevels,
    'locationPath' => $locationPath ?? null,
    'priceTypeOptions' => $priceTypeOptions,
])
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/pages/admin/location-pricing-rules/form.js') }}"></script>
@endpush
