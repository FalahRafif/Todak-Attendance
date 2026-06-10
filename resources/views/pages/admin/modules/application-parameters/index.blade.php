@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Kelola parameter global aplikasi Todak Attendace.</p></div></div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card custom-card ka-card">
            <div class="card-body">
                <h5 class="mb-2">Kuota Cuti Tahunan</h5>
                <p class="text-muted">Parameter ini dipakai saat karyawan baru dibuat dan saat HRD generate saldo cuti untuk tahun berjalan atau tahun yang dipilih di halaman saldo cuti.</p>
                <form method="POST" action="{{ route('admin.application-parameters.annual-leave-quota') }}" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-6"><label class="form-label">Maks Cuti per Tahun</label><input type="number" name="default_quota" value="{{ $annualLeaveQuota }}" min="0" max="365" class="form-control" required></div>
                    <div class="col-md-6"><button class="btn btn-primary w-100">Simpan Parameter</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card custom-card ka-card">
            <div class="card-body">
                <h5>Parameter Aktif</h5>
                <div class="mt-3"><span class="text-muted d-block">Maks Cuti Tahunan</span><h3>{{ $annualLeaveQuota }} hari</h3></div>
            </div>
        </div>
    </div>
</div>
@endsection
