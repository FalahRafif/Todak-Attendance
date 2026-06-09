@extends('layouts.admin.admin')
@section('title', $title)
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/leaflet/leaflet.css') }}">
<style>
    .ka-attendance-shell { max-width: 760px; margin: 0 auto; }
    .ka-camera-frame { position: relative; border-radius: 24px; overflow: hidden; background: #0f172a; min-height: 320px; }
    .ka-camera-frame video, .ka-camera-frame img { width: 100%; min-height: 320px; max-height: 480px; object-fit: cover; display: block; }
    .ka-camera-overlay { position: absolute; inset: auto 1rem 1rem 1rem; display: flex; justify-content: space-between; gap: .75rem; align-items: center; color: #fff; }
    .ka-progress-step { display: flex; align-items: center; gap: .75rem; padding: .85rem 1rem; border: 1px solid #e5edf7; border-radius: 16px; background: #fff; }
    .ka-progress-step.is-done { border-color: rgba(16,185,129,.35); background: rgba(16,185,129,.08); }
    .ka-step-icon { width: 34px; height: 34px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: #e2e8f0; color: #334155; font-weight: 800; }
    .ka-progress-step.is-done .ka-step-icon { background: #10b981; color: #fff; }
    .ka-sticky-submit { position: sticky; bottom: 1rem; z-index: 8; }
    .ka-location-map { min-height: 280px; border-radius: 22px; overflow: hidden; border: 1px solid #e5edf7; z-index: 1; }
    .ka-map-legend { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: .75rem; }
    .ka-map-legend span { display: inline-flex; align-items: center; gap: .4rem; font-size: .85rem; color: #64748b; }
    .ka-map-dot { width: 10px; height: 10px; border-radius: 999px; display: inline-block; }
    @media (max-width: 576px) { .ka-toolbar { align-items: flex-start; } .ka-camera-frame { border-radius: 20px; min-height: 280px; } .ka-camera-frame video, .ka-camera-frame img { min-height: 280px; } .ka-location-map { min-height: 240px; border-radius: 18px; } }
</style>
@endpush
@section('content')
@include('pages.admin.modules.partials.flash')
<div class="ka-attendance-shell">
    <div class="ka-toolbar">
        <div>
            <h2 class="ka-page-title">{{ $title }}</h2>
            <p class="ka-page-subtitle">Ikuti 2 langkah: ambil selfie, lalu ambil GPS.</p>
        </div>
        <a href="{{ route('employee.attendance') }}" class="btn btn-light">Kembali</a>
    </div>
    <div class="card custom-card ka-card mb-3">
        <div class="card-body">
            <form method="POST" action="{{ $type === 'check_in' ? route('employee.attendance.check-in.store') : route('employee.attendance.check-out.store') }}" class="row g-3" id="attendance-form">
                @csrf
                <input type="hidden" name="photo_data" id="photo_data">
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <input type="hidden" name="gps_accuracy_meter" id="gps_accuracy_meter">
                <div class="col-12">
                    <div class="ka-camera-frame">
                        <video id="camera" autoplay playsinline></video>
                        <img id="preview" class="d-none" alt="Captured photo">
                        <canvas id="snapshot" class="d-none"></canvas>
                        <div class="ka-camera-overlay">
                            <span class="badge bg-dark bg-opacity-75" id="camera-status">Kamera aktif</span>
                            <button type="button" id="switch-camera" class="btn btn-sm btn-light">Ganti Kamera</button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6"><button type="button" id="capture" class="btn btn-primary w-100">Ambil Selfie</button></div>
                <div class="col-12 col-md-6"><button type="button" id="gps" class="btn btn-outline-primary w-100">Ambil Lokasi GPS</button></div>
                <div class="col-12">
                    <div class="row g-2">
                        <div class="col-md-6"><div class="ka-progress-step" id="photo-step"><span class="ka-step-icon">1</span><div><strong>Selfie</strong><div class="text-muted small">Belum diambil</div></div></div></div>
                        <div class="col-md-6"><div class="ka-progress-step" id="gps-step"><span class="ka-step-icon">2</span><div><strong>GPS</strong><div class="text-muted small" id="gps-text">Belum diambil</div></div></div></div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Peta Lokasi</label>
                    <div id="checkout-map" class="ka-location-map" data-office-latitude="{{ $employee->workLocation?->latitude }}" data-office-longitude="{{ $employee->workLocation?->longitude }}" data-office-name="{{ $employee->workLocation?->name ?? 'Kantor' }}" data-radius="{{ $employee->workLocation?->radius_meter ?? 100 }}"></div>
                    <div class="ka-map-legend"><span><i class="ka-map-dot" style="background:#0f4c81"></i>Posisi kantor</span><span><i class="ka-map-dot" style="background:#10b981"></i>Posisi Anda</span><span>Peta hanya tampil, tidak bisa digeser manual.</span></div>
                </div>
                <div class="col-12"><label class="form-label">Catatan</label><textarea name="note" class="form-control" rows="3" placeholder="Wajib jika berada di luar radius lokasi kerja"></textarea></div>
                <div class="col-12"><div id="helper" class="alert alert-info mb-0">Ambil selfie dan GPS sebelum submit.</div></div>
                <div class="col-12 ka-sticky-submit"><button class="btn btn-primary btn-lg w-100 shadow" id="submit-attendance" disabled>Submit {{ $title }}</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('assets/libs/leaflet/leaflet.js') }}"></script>
<script>
    function initAttendanceForm() {
        var video = document.getElementById('camera');
        var preview = document.getElementById('preview');
        var canvas = document.getElementById('snapshot');
        var helper = document.getElementById('helper');
        var submit = document.getElementById('submit-attendance');
        var photoInput = document.getElementById('photo_data');
        var latitudeInput = document.getElementById('latitude');
        var longitudeInput = document.getElementById('longitude');
        var photoStep = document.getElementById('photo-step');
        var gpsStep = document.getElementById('gps-step');
        var gpsText = document.getElementById('gps-text');
        var cameraStatus = document.getElementById('camera-status');
        var stream = null;
        var facingMode = 'user';
        var mapElement = document.getElementById('checkout-map');
        var checkoutMap = null;
        var userMarker = null;
        var officeMarker = null;
        var radiusCircle = null;

        function setHelper(message, type) {
            helper.className = 'alert alert-' + type + ' mb-0';
            helper.textContent = message;
        }

        function refreshSubmitState() {
            var ready = photoInput.value && latitudeInput.value && longitudeInput.value;
            submit.disabled = !ready;
            if (ready) {
                setHelper('Semua siap. Submit absensi sekarang.', 'success');
            }
        }

        function hasValidCoordinate(latitude, longitude) {
            return Number.isFinite(latitude) && Number.isFinite(longitude) && latitude >= -90 && latitude <= 90 && longitude >= -180 && longitude <= 180;
        }

        function initCheckoutMap() {
            if (!mapElement || typeof L === 'undefined') {
                return;
            }
            var officeLatitude = parseFloat(mapElement.dataset.officeLatitude);
            var officeLongitude = parseFloat(mapElement.dataset.officeLongitude);
            var hasOfficeCoordinate = hasValidCoordinate(officeLatitude, officeLongitude);
            var center = hasOfficeCoordinate ? [officeLatitude, officeLongitude] : [-6.1753924, 106.8271528];
            checkoutMap = L.map(mapElement, {
                dragging: false,
                scrollWheelZoom: false,
                doubleClickZoom: false,
                touchZoom: false,
                boxZoom: false,
                keyboard: false,
                zoomControl: false,
                attributionControl: true
            }).setView(center, 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(checkoutMap);
            if (hasOfficeCoordinate) {
                officeMarker = L.marker([officeLatitude, officeLongitude]).addTo(checkoutMap).bindPopup(mapElement.dataset.officeName || 'Kantor');
                radiusCircle = L.circle([officeLatitude, officeLongitude], {
                    radius: parseInt(mapElement.dataset.radius, 10) || 100,
                    color: '#0f4c81',
                    fillColor: '#0f4c81',
                    fillOpacity: 0.1,
                    weight: 2
                }).addTo(checkoutMap);
            } else {
                setHelper('Koordinat kantor belum lengkap. Peta akan menampilkan posisi Anda setelah GPS diambil.', 'warning');
            }
            setTimeout(function () { checkoutMap.invalidateSize(); }, 250);
        }

        function updateCheckoutMap(latitude, longitude) {
            if (!checkoutMap) {
                return;
            }
            var position = [latitude, longitude];
            if (userMarker === null) {
                userMarker = L.circleMarker(position, {
                    radius: 8,
                    color: '#10b981',
                    fillColor: '#10b981',
                    fillOpacity: 1,
                    weight: 3
                }).addTo(checkoutMap).bindPopup('Posisi Anda');
            } else {
                userMarker.setLatLng(position);
            }
            if (officeMarker) {
                var bounds = L.latLngBounds([officeMarker.getLatLng(), userMarker.getLatLng()]);
                checkoutMap.fitBounds(bounds.pad(0.25), { animate: true, maxZoom: 17 });
            } else {
                checkoutMap.setView(position, 17, { animate: true });
            }
            setTimeout(function () { checkoutMap.invalidateSize(); }, 100);
        }

        function startCamera() {
            if (stream) {
                stream.getTracks().forEach(function (track) { track.stop(); });
            }
            navigator.mediaDevices.getUserMedia({ video: { facingMode: facingMode }, audio: false }).then(function (activeStream) {
                stream = activeStream;
                video.srcObject = activeStream;
                cameraStatus.textContent = facingMode === 'user' ? 'Kamera depan' : 'Kamera belakang';
            }).catch(function () {
                setHelper('Kamera tidak dapat diakses. Pastikan izin kamera aktif.', 'danger');
            });
        }

        initCheckoutMap();
        startCamera();

        document.getElementById('switch-camera').addEventListener('click', function () {
            facingMode = facingMode === 'user' ? 'environment' : 'user';
            startCamera();
        });

        document.getElementById('capture').addEventListener('click', function () {
            if (!video.videoWidth) {
                setHelper('Kamera belum siap.', 'warning');
                return;
            }
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            photoInput.value = canvas.toDataURL('image/jpeg', .9);
            preview.src = photoInput.value;
            preview.classList.remove('d-none');
            video.classList.add('d-none');
            photoStep.classList.add('is-done');
            photoStep.querySelector('.small').textContent = 'Selfie siap';
            setHelper('Selfie berhasil. Lanjut ambil GPS.', 'info');
            refreshSubmitState();
        });

        document.getElementById('gps').addEventListener('click', function () {
            if (!navigator.geolocation) {
                setHelper('Browser tidak mendukung GPS.', 'danger');
                return;
            }
            setHelper('Mengambil GPS akurasi tinggi...', 'info');
            navigator.geolocation.getCurrentPosition(function (position) {
                latitudeInput.value = position.coords.latitude;
                longitudeInput.value = position.coords.longitude;
                document.getElementById('gps_accuracy_meter').value = position.coords.accuracy;
                gpsStep.classList.add('is-done');
                gpsText.textContent = 'Akurasi ' + Math.round(position.coords.accuracy) + ' meter';
                updateCheckoutMap(position.coords.latitude, position.coords.longitude);
                refreshSubmitState();
            }, function () {
                setHelper('GPS tidak dapat diakses. Pastikan izin lokasi aktif.', 'danger');
                refreshSubmitState();
            }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
        });

        document.getElementById('attendance-form').addEventListener('submit', function (event) {
            if (submit.disabled) {
                event.preventDefault();
                setHelper('Ambil selfie dan GPS sebelum submit.', 'warning');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAttendanceForm);
    } else {
        initAttendanceForm();
    }
</script>
@endpush
