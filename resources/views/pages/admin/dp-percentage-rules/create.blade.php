@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@include('pages.admin.partials.page-header', [
    'heading' => 'Tambah Aturan Persen DP',
    'summary' => 'Buat aturan persentase DP baru untuk tipe paket tertentu.',
    'actions' => [
        ['label' => 'Kembali ke Daftar', 'url' => route('admin.dp-percentage-rules'), 'class' => 'btn btn-outline-primary btn-sm'],
    ],
])

@include('pages.admin.dp-percentage-rules._form', [
    'formTitle' => 'Form Tambah Aturan',
    'formAction' => route('api.admin.dp-percentage-rules.store'),
    'submitLabel' => 'Simpan Aturan',
    'packageTypeOptions' => $packageTypeOptions,
])
@endsection
