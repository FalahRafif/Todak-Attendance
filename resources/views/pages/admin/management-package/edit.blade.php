@extends('layouts.admin.admin')

@section('title', $title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/pages/admin/management-package/form.css') }}">
@endpush

@section('content')
@include('pages.admin.partials.page-header', [
    'heading' => 'Edit Paket',
    'summary' => 'Perbarui data paket layanan termasuk benefit dan thumbnail.',
    'actions' => [
        ['label' => 'Kembali ke Daftar', 'url' => route('admin.packages'), 'class' => 'btn btn-outline-primary btn-sm'],
    ],
])

@include('pages.admin.management-package._form', [
    'formTitle' => 'Form Edit Paket',
    'formAction' => route('api.admin.packages.update', $managedPackage),
    'submitLabel' => 'Simpan Perubahan',
    'managedPackage' => $managedPackage,
    'statusOptions' => $statusOptions,
    'packageTypeOptions' => $packageTypeOptions,
])
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="{{ asset('assets/pages/admin/management-package/form.js') }}"></script>
@endpush
