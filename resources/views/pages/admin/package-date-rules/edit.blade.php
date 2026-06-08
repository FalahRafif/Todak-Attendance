@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@include('pages.admin.partials.page-header', [
    'heading' => 'Edit Aturan Waktu Paket',
    'summary' => 'Perbarui aturan batas waktu paket.',
    'actions' => [
        ['label' => 'Kembali ke Daftar', 'url' => route('admin.package-date-rules'), 'class' => 'btn btn-outline-primary btn-sm'],
    ],
])

@include('pages.admin.package-date-rules._form', [
    'formTitle' => 'Form Edit Aturan',
    'formAction' => route('api.admin.package-date-rules.update', $managedRule),
    'submitLabel' => 'Simpan Perubahan',
    'managedRule' => $managedRule,
])
@endsection
