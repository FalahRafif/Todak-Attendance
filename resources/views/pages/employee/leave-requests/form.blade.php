@extends('layouts.admin.admin')
@section('title', $title)
@push('styles')
<style>.ka-mobile-shell{max-width:820px;margin:0 auto}.ka-form-panel{border-radius:22px;border:1px solid #e5edf7;background:#fff;box-shadow:0 10px 28px rgba(15,76,129,.06)}.ka-total-days{border-radius:18px;background:#eff6ff;color:#0f4c81;padding:1rem}</style>
@endpush
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-mobile-shell">
    <div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Isi periode dan alasan pengajuan secara jelas.</p></div><a href="{{ route('employee.leave-requests') }}" class="btn btn-light">Kembali</a></div>
    <div class="ka-form-panel p-3 p-md-4">
        <form method="POST" action="{{ route('employee.leave-requests.store') }}" class="row g-3" id="leave-form" enctype="multipart/form-data">
            @csrf
            @if($leaveBalance)
            <div class="col-12"><div class="alert alert-info"><strong>Sisa Cuti Tahunan {{ $leaveBalance->year }}:</strong> {{ $leaveBalance->remaining_quota }} hari dari {{ $leaveBalance->total_quota }} hari (Terpakai {{ $leaveBalance->used_quota }} hari)</div></div>
            @endif
            <div class="col-md-4"><label class="form-label">Jenis Pengajuan</label><select name="leave_type_id" class="form-control" required>@foreach($leaveTypes as $type)<option value="{{ $type->id }}">{{ friendly_label($type->description) }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Tanggal Mulai</label><input type="date" name="start_date" id="start_date" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Tanggal Selesai</label><input type="date" name="end_date" id="end_date" class="form-control" required><div class="form-text">Untuk cuti 1 hari, isi tanggal selesai sama dengan tanggal mulai.</div></div>
            <div class="col-12"><div class="ka-total-days"><strong id="total-days">0 hari</strong><div class="small" id="total-days-note">Total hari dihitung otomatis dari tanggal mulai dan selesai.</div></div></div>
            <div class="col-12"><label class="form-label">Alasan</label><textarea name="reason" rows="4" class="form-control" placeholder="Contoh: Izin keluarga, sakit, atau cuti tahunan" required></textarea></div>
            <div class="col-12"><label class="form-label">Attachment Pendukung</label><input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"><div class="form-text">Opsional. Maksimal 5 MB. Format: JPG, PNG, PDF, DOC, DOCX.</div></div>
            <div class="col-12"><button class="btn btn-primary btn-lg w-100">Kirim Pengajuan</button></div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var start = document.getElementById('start_date');
        var end = document.getElementById('end_date');
        var total = document.getElementById('total-days');
        var note = document.getElementById('total-days-note');
        function syncDays() {
            if (start.value && !end.value) {
                end.value = start.value;
            }
            if (!start.value || !end.value) { total.textContent = '0 hari'; note.textContent = 'Total hari dihitung otomatis dari tanggal mulai dan selesai.'; return; }
            var startDate = new Date(start.value);
            var endDate = new Date(end.value);
            var diff = Math.floor((endDate - startDate) / 86400000) + 1;
            total.textContent = diff > 0 ? diff + ' hari' : 'Tanggal tidak valid';
            note.textContent = diff === 1 ? 'Pengajuan 1 hari. Tanggal mulai dan selesai sama.' : 'Pengajuan multi-hari. Pastikan rentang tanggal sudah benar.';
        }
        start.addEventListener('change', syncDays);
        end.addEventListener('change', syncDays);
    });
</script>
@endpush
