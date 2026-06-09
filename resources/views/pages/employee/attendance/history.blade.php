@extends('layouts.admin.admin')
@section('title', $title)
@push('styles')
<style>
    .ka-mobile-shell { max-width: 860px; margin: 0 auto; }
    .ka-history-card { display: block; border: 1px solid #e5edf7; border-radius: 18px; padding: 1rem; background: #fff; text-decoration: none; color: inherit; box-shadow: 0 8px 22px rgba(15,76,129,.05); }
    .ka-history-date { width: 58px; height: 58px; border-radius: 16px; background: #eff6ff; color: #0f4c81; display: flex; flex-direction: column; justify-content: center; align-items: center; flex: 0 0 auto; }
    .ka-history-date strong { font-size: 1.2rem; line-height: 1; }
    .ka-history-date span { font-size: .72rem; text-transform: uppercase; }
    @media (max-width: 576px) { .ka-history-card { border-radius: 16px; } }
</style>
@endpush
@section('content')
<div class="ka-mobile-shell">
    <div class="ka-toolbar">
        <div>
            <h2 class="ka-page-title">{{ $title }}</h2>
            <p class="ka-page-subtitle">Riwayat absensi bulan berjalan.</p>
        </div>
    </div>
    <div class="card custom-card ka-card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-center">
                <div class="col-6 col-md-4"><input type="month" name="month" value="{{ $month->format('Y-m') }}" class="form-control"></div>
                <div class="col-6 col-md-4"><select name="status_id" class="form-control"><option value="">Semua Status</option>@foreach($statuses as $status)<option value="{{ $status->id }}" @selected((int) request('status_id') === $status->id)>{{ friendly_label($status->description) }}</option>@endforeach</select></div>
                <div class="col-12 col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
            </form>
        </div>
    </div>
    <div class="d-grid gap-3">
        @forelse($items as $item)
            @php($lateTolerance = $item->shift?->late_tolerance_minutes ?? 0)
            @php($isLateOutsideTolerance = $item->late_minutes > $lateTolerance)
            @php($workHours = intdiv((int) $item->total_work_minutes, 60))
            @php($workMinutes = (int) $item->total_work_minutes % 60)
            <a href="{{ route('employee.attendance.history.show', $item->id) }}" class="ka-history-card">
                <div class="d-flex gap-3 align-items-center">
                    <div class="ka-history-date"><strong>{{ $item->attendance_date?->format('d') }}</strong><span>{{ $item->attendance_date?->format('M') }}</span></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">{{ friendly_label($item->status?->description) }}</div>
                        <div class="text-muted small">{{ $item->workLocation?->name ?? '-' }} · {{ $item->check_out_at ? $workHours.'j '.$workMinutes.'m kerja' : 'Belum absen pulang' }}</div>
                        <div class="mt-2 d-flex gap-2 flex-wrap"><span class="badge bg-primary-transparent text-primary">Masuk {{ $item->check_in_at?->format('H:i') ?? '--:--' }}</span><span class="badge bg-secondary-transparent text-secondary">Pulang {{ $item->check_out_at?->format('H:i') ?? '--:--' }}</span><span class="badge {{ $isLateOutsideTolerance ? 'bg-danger-transparent text-danger' : 'bg-success-transparent text-success' }}">{{ $item->late_minutes > 0 ? 'Telat '.$item->late_minutes.'m'.($isLateOutsideTolerance ? ' > toleransi' : ' toleransi') : 'Tepat waktu' }}</span>@if($item->is_need_approval)<span class="badge bg-warning-transparent text-warning">Perlu Dicek HRD</span>@endif</div>
                    </div>
                    <span class="text-muted">›</span>
                </div>
            </a>
        @empty
            <div class="card custom-card ka-card"><div class="card-body text-center text-muted py-5">Belum ada riwayat pada bulan ini.</div></div>
        @endforelse
    </div>
    <div class="mt-3">{{ $items->links() }}</div>
</div>
@endsection
