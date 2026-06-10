@extends('layouts.admin.admin')
@section('title', $title)
@include('pages.hrd.partials.mobile-styles')
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-hrd-shell">
    <div class="ka-hrd-toolbar">
        <div>
            <h2 class="ka-page-title">{{ $title }}</h2>
            <p class="ka-page-subtitle">Pantau dan atur saldo cuti karyawan tahun {{ $year }}.</p>
        </div>
        <form method="POST" action="{{ route('hrd.leave-balances.generate') }}">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="submit" class="btn btn-success">Generate Saldo {{ $year }}</button>
        </form>
    </div>
    <div class="card custom-card ka-card mb-3">
        <div class="card-body">
            <form class="row g-2 ka-hrd-filter">
                <div class="col-6 col-md-2"><input type="number" name="year" value="{{ $year }}" class="form-control" placeholder="Tahun"></div>
                <div class="col-6 col-md-3"><select name="department_id" class="form-control"><option value="">Semua Dept</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected((string) request('department_id') === (string) $d->id)>{{ $d->name }}</option>@endforeach</select></div>
                <div class="col-6 col-md-3"><input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="Cari karyawan..."></div>
                <div class="col-6 col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
            </form>
        </div>
    </div>
    <div class="card custom-card ka-card">
        <div class="card-body">
            <div class="ka-hrd-card-list">
                @forelse($items as $item)
                    <div class="ka-hrd-item">
                        <div class="d-flex justify-content-between gap-2">
                            <div>
                                <div class="ka-hrd-item-title">{{ $item->employee?->full_name ?? '-' }}</div>
                                <div class="ka-hrd-meta">{{ $item->employee?->department?->name ?? '-' }} · {{ friendly_label($item->leaveType?->description) }}</div>
                            </div>
                            <div class="text-end">
                                <span class="ka-hrd-pill">{{ $item->year }}</span>
                                <div class="mt-1"><strong class="{{ $item->remaining_quota <= 0 ? 'text-danger' : 'text-success' }}">Sisa {{ $item->remaining_quota }}</strong> / {{ $item->total_quota }} hari</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-2"><span class="text-muted small">Terpakai {{ $item->used_quota }}</span><button type="button" class="btn btn-sm btn-outline-primary ms-auto" data-bs-toggle="modal" data-bs-target="#adjustModal{{ $item->id }}">Adjust Kuota</button></div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">Belum ada saldo cuti untuk tahun {{ $year }}. Klik "Generate Saldo" untuk membuat otomatis.</div>
                @endforelse
            </div>
            <div class="table-responsive ka-hrd-table">
                <table class="table ka-table">
                    <thead><tr><th>Karyawan</th><th>Dept</th><th>Jenis</th><th>Tahun</th><th>Kuota</th><th>Terpakai</th><th>Sisa</th><th></th></tr></thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->employee?->full_name ?? '-' }}</td>
                                <td>{{ $item->employee?->department?->name ?? '-' }}</td>
                                <td>{{ friendly_label($item->leaveType?->description) }}</td>
                                <td>{{ $item->year }}</td>
                                <td>{{ $item->total_quota }}</td>
                                <td>{{ $item->used_quota }}</td>
                                <td><strong class="{{ $item->remaining_quota <= 0 ? 'text-danger' : '' }}">{{ $item->remaining_quota }}</strong></td>
                                <td><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#adjustModal{{ $item->id }}">Adjust</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada saldo cuti.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">{{ $items->links() }}</div>
    </div>
    @foreach($items as $item)
        <div class="modal fade" id="adjustModal{{ $item->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Adjust Saldo Cuti - {{ $item->employee?->full_name ?? '-' }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <form method="POST" action="{{ route('hrd.leave-balances.adjust', $item->id) }}">
                        @csrf
                        <div class="modal-body">
                            <p>Kuota saat ini: <strong>{{ $item->total_quota }}</strong> hari · Terpakai: <strong>{{ $item->used_quota }}</strong> · Sisa: <strong>{{ $item->remaining_quota }}</strong></p>
                            <div class="mb-3"><label class="form-label">Tambah Kuota Tambahan (hari)</label><input type="number" name="additional_quota" class="form-control" placeholder="Contoh: 2 (sebagai reward)" min="0"><div class="form-text">Menambah kuota di atas kuota saat ini. Sisa akan bertambah otomatis.</div></div>
                            <div class="mb-3"><label class="form-label">Atau Set Total Kuota Langsung</label><input type="number" name="total_quota" class="form-control" placeholder="Contoh: 14" min="0"><div class="form-text">Mengubah total kuota secara langsung. Sisa dihitung ulang dari total dikurangi terpakai.</div></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
