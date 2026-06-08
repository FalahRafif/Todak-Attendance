@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@include('pages.admin.partials.page-header', [
    'heading' => 'Tambah Aturan Waktu Paket',
    'summary' => 'Buat aturan batas waktu paket baru dengan format H+X atau H-X.',
    'actions' => [
        ['label' => 'Kembali ke Daftar', 'url' => route('admin.package-date-rules'), 'class' => 'btn btn-outline-primary btn-sm'],
    ],
])

@include('pages.admin.package-date-rules._form', [
    'formTitle' => 'Form Tambah Aturan',
    'formAction' => route('api.admin.package-date-rules.store'),
    'submitLabel' => 'Simpan Aturan',
])
@endsection
