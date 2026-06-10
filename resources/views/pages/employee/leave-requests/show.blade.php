@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-mobile-shell" style="max-width:820px;margin:0 auto">
    <div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Detail status pengajuan Anda.</p></div><a href="{{ route('employee.leave-requests') }}" class="btn btn-light">Kembali</a></div>
    <div class="card custom-card ka-card"><div class="card-body p-4"><div class="d-flex justify-content-between gap-3 flex-wrap mb-3"><div><div class="text-muted small">Jenis Pengajuan</div><h4>{{ friendly_label($item->leaveType?->description) }}</h4></div><span class="ka-badge ka-badge-primary">{{ friendly_label($item->status?->description) }}</span></div><div class="row g-3"><div class="col-md-4"><b>Mulai</b><div>{{ $item->start_date?->format('d M Y') }}</div></div><div class="col-md-4"><b>Selesai</b><div>{{ $item->end_date?->format('d M Y') }}</div></div><div class="col-md-4"><b>Total Hari</b><div>{{ $item->total_days }} hari</div></div><div class="col-12"><b>Alasan</b><div>{{ $item->reason }}</div></div><div class="col-12"><b>Attachment</b><div>@if($attachmentUrl)<a href="{{ $attachmentUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mt-2">Lihat Attachment</a>@else - @endif</div></div><div class="col-12"><b>Alasan Ditolak</b><div>{{ $item->rejected_reason ?? '-' }}</div></div></div>@if($item->status?->description === 'pending')<form method="POST" action="{{ route('employee.leave-requests.cancel', $item->id) }}" class="mt-4">@csrf<button class="btn btn-danger w-100">Batalkan Pengajuan</button></form>@endif</div></div>
</div>
@endsection
