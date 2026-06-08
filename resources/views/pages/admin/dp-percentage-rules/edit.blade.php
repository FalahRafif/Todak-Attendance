@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@include('pages.admin.partials.page-header', [
    'heading' => 'Edit Aturan Persen DP',
    'summary' => 'Perbarui persentase atau tipe paket untuk aturan DP.',
    'actions' => [
        ['label' => 'Kembali ke Daftar', 'url' => route('admin.dp-percentage-rules'), 'class' => 'btn btn-outline-primary btn-sm'],
    ],
])

@include('pages.admin.dp-percentage-rules._form', [
    'formTitle' => 'Form Edit Aturan',
    'formAction' => route('api.admin.dp-percentage-rules.update', $managedRule),
    'submitLabel' => 'Simpan Perubahan',
    'managedRule' => $managedRule,
    'packageTypeOptions' => $packageTypeOptions,
])
@endsection
