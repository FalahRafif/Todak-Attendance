@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@include('pages.admin.partials.page-header', [
    'heading' => 'Tambah Aturan Waktu Pembayaran',
    'summary' => 'Buat aturan baru untuk batas waktu pembayaran DP atau pelunasan.',
    'actions' => [
        ['label' => 'Kembali ke Daftar', 'url' => route('admin.payment-date-rules'), 'class' => 'btn btn-outline-primary btn-sm'],
    ],
])

@include('pages.admin.payment-date-rules._form', [
    'formTitle' => 'Form Tambah Aturan',
    'formAction' => route('api.admin.payment-date-rules.store'),
    'submitLabel' => 'Simpan Aturan',
])
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
@endpush
