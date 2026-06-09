@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-mobile-shell" style="max-width:820px;margin:0 auto">
    <div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Masukkan jam yang seharusnya tercatat.</p></div><a href="{{ route('employee.attendance-corrections') }}" class="btn btn-light">Kembali</a></div>
    <div class="card custom-card ka-card"><div class="card-body p-4"><form method="POST" action="{{ route('employee.attendance-corrections.store') }}" class="row g-3">@csrf<div class="col-md-4"><label class="form-label">Tanggal Koreksi</label><input type="date" name="correction_date" class="form-control" required></div><div class="col-md-4"><label class="form-label">Jam Masuk yang Diajukan</label><input type="datetime-local" name="requested_check_in_at" class="form-control"></div><div class="col-md-4"><label class="form-label">Jam Pulang yang Diajukan</label><input type="datetime-local" name="requested_check_out_at" class="form-control"></div><div class="col-12"><div class="alert alert-info mb-0">Isi salah satu atau keduanya. HRD akan mengecek dan memberi keputusan untuk koreksi ini.</div></div><div class="col-12"><label class="form-label">Alasan</label><textarea name="reason" rows="4" class="form-control" placeholder="Jelaskan alasan koreksi, misalnya lupa absen pulang atau GPS bermasalah" required></textarea></div><div class="col-12"><button class="btn btn-primary btn-lg w-100">Kirim Koreksi</button></div></form></div></div>
</div>
@endsection
