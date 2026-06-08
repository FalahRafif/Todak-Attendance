@extends('layouts.admin.admin')

@section('title', $title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/pages/admin/management-package/form.css') }}">
@endpush

@section('content')
@include('pages.admin.partials.page-header', [
    'heading' => 'Tambah Paket',
    'summary' => 'Buat paket layanan baru untuk kebutuhan wedding atau non-wedding.',
    'actions' => [
        ['label' => 'Kembali ke Daftar', 'url' => route('admin.packages'), 'class' => 'btn btn-outline-primary btn-sm'],
    ],
])

@include('pages.admin.management-package._form', [
    'formTitle' => 'Form Tambah Paket',
    'formAction' => route('api.admin.packages.store'),
    'submitLabel' => 'Simpan Paket',
    'statusOptions' => $statusOptions,
    'packageTypeOptions' => $packageTypeOptions,
])
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="{{ asset('assets/pages/admin/management-package/form.js') }}"></script>
@endpush
