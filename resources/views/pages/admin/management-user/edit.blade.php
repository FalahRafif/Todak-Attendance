@extends('layouts.admin.admin')

@section('title', $title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/pages/admin/management-user/form.css') }}">
@endpush

@section('content')
@include('pages.admin.partials.page-header', [
    'heading' => 'Edit Akun Internal',
    'summary' => 'Perbarui data akun internal termasuk role dan foto profile (opsional).',
    'actions' => [
        ['label' => 'Kembali ke Daftar', 'url' => route('admin.users'), 'class' => 'btn btn-outline-primary btn-sm'],
    ],
])

@include('pages.admin.management-user._form', [
    'formTitle' => 'Form Edit Akun',
    'formAction' => route('api.admin.users.update', $managedUser),
    'submitLabel' => 'Simpan Perubahan',
    'managedUser' => $managedUser,
    'profileImageUrl' => $profileImageUrl,
])
@endsection

@push('scripts')
    <script src="{{ asset('assets/pages/admin/management-user/form.js') }}"></script>
@endpush
