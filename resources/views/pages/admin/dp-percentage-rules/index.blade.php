@extends('layouts.admin.admin')

@section('title', $title)

@section('content')
@php
    $actions = [
        // ['label' => 'Tambah Aturan', 'url' => route('admin.dp-percentage-rules.create'), 'class' => 'btn btn-primary btn-sm'],
    ];
@endphp

@include('pages.admin.partials.page-header', [
    'heading' => 'Aturan Persen DP',
    'summary' => 'Kelola persentase DP (Down Payment) berdasarkan tipe paket wedding dan non-wedding.',
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
        ['class' => 'alert-info', 'text' => 'Persentase DP digunakan untuk menghitung nominal down payment. Wedding = 15%, Non-Wedding = 10%.'],
    ],
])

<div class="card custom-card mb-0">
    <div class="card-header">
        <h5 class="card-title mb-0">Daftar Aturan Persen DP</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Deskripsi</th>
                        <th>Tipe Paket</th>
                        <th>Persentase</th>
                        <th>Diperbarui</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($rules ?? collect()) as $rule)
                        @php
                            $typeName = trim((string) ($rule->type?->description ?? '-'));
                            $typeCode = strtoupper((string) ($rule->type?->code ?? ''));
                            $typeBadge = match ($typeCode) {
                                'PKT_WEDDING' => 'bg-pink-transparent text-pink',
                                'PKT_NON_WEDDING' => 'bg-info-transparent text-info',
                                default => 'bg-secondary-transparent text-secondary',
                            };
                            $percentage = (float) ($rule->value ?? 0);
                        @endphp
                        <tr>
                            <td><code>{{ $rule->code }}</code></td>
                            <td>{{ $rule->description }}</td>
                            <td><span class="badge rounded-pill {{ $typeBadge }}">{{ $typeName }}</span></td>
                            <td>
                                <span class="fw-semibold text-primary">{{ number_format($percentage, 0) }}%</span>
                            </td>
                            <td>{{ $rule->updated_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="text-end">
                                <div class="btn-list justify-content-end">
                                    <a href="{{ route('admin.dp-percentage-rules.edit', $rule) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="{{ route('api.admin.dp-percentage-rules.destroy', $rule) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus aturan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data aturan persen DP.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
