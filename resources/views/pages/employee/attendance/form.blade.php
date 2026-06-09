@extends('layouts.admin.admin')
@section('title', $title)
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-toolbar"><div><h2 class="ka-page-title">{{ $title }}</h2><p class="ka-page-subtitle">Foto wajib dari kamera dan GPS wajib aktif.</p></div><a href="{{ route('employee.attendance') }}" class="btn btn-light">Back</a></div>
<div class="card custom-card ka-card"><div class="card-body"><form method="POST" action="{{ $type === 'check_in' ? route('employee.attendance.check-in.store') : route('employee.attendance.check-out.store') }}" class="row g-3" id="attendance-form">@csrf<input type="hidden" name="photo_data" id="photo_data"><input type="hidden" name="latitude" id="latitude"><input type="hidden" name="longitude" id="longitude"><input type="hidden" name="gps_accuracy_meter" id="gps_accuracy_meter"><div class="col-12"><video id="camera" autoplay playsinline class="w-100 rounded bg-dark" style="max-height:360px"></video><canvas id="snapshot" class="d-none"></canvas></div><div class="col-md-6"><button type="button" id="capture" class="btn btn-outline-primary w-100">Ambil Foto</button></div><div class="col-md-6"><button type="button" id="gps" class="btn btn-outline-primary w-100">Ambil GPS</button></div><div class="col-12"><textarea name="note" class="form-control" placeholder="Catatan jika outside radius"></textarea></div><div class="col-12"><div id="helper" class="text-muted small">Ambil foto dan GPS sebelum submit.</div></div><div class="col-12"><button class="btn btn-primary" id="submit-attendance" disabled>Submit {{ $title }}</button></div></form></div></div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var video = document.getElementById('camera');
    var canvas = document.getElementById('snapshot');
    var helper = document.getElementById('helper');
    var submit = document.getElementById('submit-attendance');
    var photoInput = document.getElementById('photo_data');
    var latitudeInput = document.getElementById('latitude');
    var longitudeInput = document.getElementById('longitude');

    function refreshSubmitState() {
        submit.disabled = !(photoInput.value && latitudeInput.value && longitudeInput.value);
    }

    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false }).then(function (stream) {
        video.srcObject = stream;
    }).catch(function () {
        helper.textContent = 'Kamera tidak dapat diakses. Pastikan izin kamera aktif.';
    });

    document.getElementById('capture').addEventListener('click', function () {
        if (!video.videoWidth) {
            helper.textContent = 'Kamera belum siap.';
            return;
        }
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        photoInput.value = canvas.toDataURL('image/jpeg', .9);
        helper.textContent = 'Foto berhasil diambil. Lanjut ambil GPS.';
        refreshSubmitState();
    });

    document.getElementById('gps').addEventListener('click', function () {
        if (!navigator.geolocation) {
            helper.textContent = 'Browser tidak mendukung GPS.';
            return;
        }
        helper.textContent = 'Mengambil GPS...';
        navigator.geolocation.getCurrentPosition(function (position) {
            latitudeInput.value = position.coords.latitude;
            longitudeInput.value = position.coords.longitude;
            document.getElementById('gps_accuracy_meter').value = position.coords.accuracy;
            helper.textContent = 'GPS berhasil diambil.';
            refreshSubmitState();
        }, function () {
            helper.textContent = 'GPS tidak dapat diakses. Pastikan izin lokasi aktif.';
            refreshSubmitState();
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
    });

    document.getElementById('attendance-form').addEventListener('submit', function (event) {
        if (submit.disabled) {
            event.preventDefault();
            helper.textContent = 'Ambil foto dan GPS sebelum submit.';
        }
    });
});
</script>
@endpush
