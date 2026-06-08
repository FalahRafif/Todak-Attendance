@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@include('pages.admin.partials.page-header', [
    'heading' => 'Edit Aturan Waktu Pembayaran',
    'summary' => 'Perbarui nilai aturan batas waktu pembayaran.',
    'actions' => [
        ['label' => 'Kembali ke Daftar', 'url' => route('admin.payment-date-rules'), 'class' => 'btn btn-outline-primary btn-sm'],
    ],
])

@include('pages.admin.payment-date-rules._form', [
    'formTitle' => 'Form Edit Aturan',
    'formAction' => route('api.admin.payment-date-rules.update', $managedRule),
    'submitLabel' => 'Simpan Perubahan',
    'managedRule' => $managedRule,
])
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
@endpush
