@extends('layouts.admin.admin')
@section('title', $title)
@include('pages.hrd.partials.mobile-styles')
@section('content')
@include('pages.admin.modules.partials.flash')
@php($statusColors = ['pending' => 'bg-warning text-dark', 'approved' => 'bg-success', 'rejected' => 'bg-danger', 'cancelled' => 'bg-secondary'])
@php($isPending = $item->status?->description === 'pending')
<div class="ka-hrd-shell">
    <div class="ka-hrd-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Cek detail dan beri keputusan untuk pengajuan.</p></div><a href="{{ route('hrd.leave-requests') }}" class="btn btn-light">Kembali</a></div>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card custom-card ka-card"><div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3"><div><h5 class="mb-1">{{ $item->employee?->full_name ?? '-' }}</h5><p class="text-muted mb-0">{{ $item->employee?->department?->name ?? '-' }}</p></div><span class="badge {{ $statusColors[$item->status?->description] ?? 'bg-secondary' }} fs-6">{{ friendly_label($item->status?->description) }}</span></div>
                <div class="row g-3">
                    <div class="col-6 col-md-4"><div class="ka-detail-box"><b>Jenis</b><div>{{ friendly_label($item->leaveType?->description) }}</div></div></div>
                    <div class="col-6 col-md-4"><div class="ka-detail-box"><b>Periode</b><div>{{ $item->start_date?->format('d M Y') }} - {{ $item->end_date?->format('d M Y') }}</div></div></div>
                    <div class="col-6 col-md-4"><div class="ka-detail-box"><b>Total Hari</b><div>{{ $item->total_days }}</div></div></div>
                    <div class="col-12"><div class="ka-detail-box"><b>Alasan</b><div>{{ $item->reason ?? '-' }}</div></div></div>
                    <div class="col-12"><div class="ka-detail-box"><b>Attachment</b><div>@if($attachmentUrl)<a href="{{ $attachmentUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mt-2">Lihat Attachment</a>@else - @endif</div></div></div>
                    @if($item->rejected_reason)<div class="col-12"><div class="ka-detail-box"><b>Alasan Ditolak</b><div>{{ $item->rejected_reason }}</div></div></div>@endif
                    <div class="col-12"><div class="ka-detail-box"><b>Diajukan</b><div>{{ $item->created_at?->format('d M Y H:i') }}</div></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card custom-card ka-card"><div class="card-body">
                @if($isPending)
                    <h5>Keputusan</h5><p class="text-muted">Status: <b>Menunggu Persetujuan</b></p>
                    <form method="POST" action="{{ route('hrd.leave-requests.approve', $item->id) }}" class="mb-2">@csrf<textarea name="approval_note" class="form-control mb-2" placeholder="Catatan persetujuan"></textarea><button class="btn btn-success w-100">Setujui</button></form>
                    <form method="POST" action="{{ route('hrd.leave-requests.reject', $item->id) }}">@csrf<textarea name="rejected_reason" class="form-control mb-2" placeholder="Alasan ditolak" required></textarea><button class="btn btn-danger w-100">Tolak</button></form>
                @else
                    <h5>Info Keputusan</h5>
                    @if($item->approved_by)<p class="mb-1"><b>{{ $item->status?->description === 'rejected' ? 'Ditolak oleh' : 'Diputuskan oleh' }}:</b> {{ \App\Models\User::find($item->approved_by)?->name ?? '-' }}</p>@endif
                    @if($item->approved_at)<p class="mb-1"><b>Waktu:</b> {{ $item->approved_at?->format('d M Y H:i') }}</p>@endif
                    @if($item->approval_note)<p class="mb-1"><b>Catatan:</b> {{ $item->approval_note }}</p>@endif
                    @if($item->rejected_reason)<p class="mb-1"><b>Alasan Ditolak:</b> {{ $item->rejected_reason }}</p>@endif
                    <div class="alert alert-light mt-3 mb-0 text-center"><span class="badge {{ $statusColors[$item->status?->description] ?? 'bg-secondary' }} fs-6">{{ friendly_label($item->status?->description) }}</span><div class="text-muted small mt-2">Pengajuan ini sudah diputuskan.</div></div>
                @endif
            </div></div>
        </div>
    </div>
</div>
@endsection
