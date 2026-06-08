@extends('layouts.admin.admin')

@section('title', $title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/pages/admin/management-user/form.css') }}">
@endpush

@section('content')
@include('pages.admin.partials.page-header', [
    'heading' => 'Tambah Akun Internal',
    'summary' => 'Buat akun baru untuk user internal dengan role Admin atau Petugas.',
    'actions' => [
        ['label' => 'Kembali ke Daftar', 'url' => route('admin.users'), 'class' => 'btn btn-outline-primary btn-sm'],
    ],
])

@include('pages.admin.management-user._form', [
    'formTitle' => 'Form Tambah Akun',
    'formAction' => route('api.admin.users.store'),
    'submitLabel' => 'Simpan Akun',
    'profileImageUrl' => null,
])
@endsection

@push('scripts')
    <script src="{{ asset('assets/pages/admin/management-user/form.js') }}"></script>
@endpush
