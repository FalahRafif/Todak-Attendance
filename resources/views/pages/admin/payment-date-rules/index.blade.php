@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@php
    $actions = [
        // ['label' => 'Tambah Aturan', 'url' => route('admin.payment-date-rules.create'), 'class' => 'btn btn-primary btn-sm'],
    ];
@endphp

@include('pages.admin.partials.page-header', [
    'heading' => 'Aturan Waktu Pembayaran',
    'summary' => 'Kelola aturan batas waktu pembayaran DP dan pelunasan berdasarkan hari (H+X / H-X).',
    'actions' => $actions,
])

@if (session('status'))
    <div class="alert alert-success mb-3" role="alert">{{ session('status') }}</div>
@endif

@if ($errors->has('general'))
    <div class="alert alert-danger mb-3" role="alert">{{ $errors->first('general') }}</div>
@endif

@include('pages.admin.partials.alerts', [
    'alerts' => [
        ['class' => 'alert-info', 'text' => 'H+X = X hari setelah approval/penawaran dibuat. H-X = X hari sebelum tanggal acara booking.'],
    ],
])

<div class="card custom-card mb-0">
    <div class="card-header">
        <h5 class="card-title mb-0">Daftar Aturan Waktu Pembayaran</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Deskripsi</th>
                        <th>Nilai</th>
                        <th>Keterangan</th>
                        <th>Diperbarui</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($rules ?? collect()) as $rule)
                        @php
                            $value = strtoupper(trim((string) ($rule->value ?? '')));
                            $isHPlus = str_starts_with($value, 'H+');
                            $valueBadge = $isHPlus
                                ? 'bg-info-transparent text-info'
                                : 'bg-warning-transparent text-warning';
                            $noteText = $resolveValueNote($value);
                        @endphp
                        <tr>
                            <td><code>{{ $rule->code }}</code></td>
                            <td>{{ $rule->description }}</td>
                            <td><span class="badge rounded-pill {{ $valueBadge }}">{{ $value }}</span></td>
                            <td><small class="text-muted">{{ $noteText }}</small></td>
                            <td>{{ $rule->updated_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="text-end">
                                <div class="btn-list justify-content-end">
                                    <a href="{{ route('admin.payment-date-rules.edit', $rule) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="{{ route('api.admin.payment-date-rules.destroy', $rule) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus aturan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data aturan waktu pembayaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
