@extends('layouts.admin.admin')
@section('title', $title)
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/leaflet/leaflet.css') }}">
<style>
    .ka-location-map {
        min-height: 420px;
        border: 1px solid #dbe4f0;
        border-radius: 16px;
        overflow: hidden;
        z-index: 1;
    }

    .ka-coordinate-card {
        border: 1px solid #e5edf7;
        border-radius: 14px;
        background: #f8fbff;
        padding: 1rem;
    }
</style>
@endpush
@section('content')
@include('pages.admin.modules.partials.flash')
@php($isEdit = $item !== null)
@php($defaultLatitude = old('latitude', $item?->latitude ?? -6.1753924))
@php($defaultLongitude = old('longitude', $item?->longitude ?? 106.8271528))
<div class="ka-toolbar">
    <div>
        <h2 class="ka-page-title">{{ $title }}</h2>
        <p class="ka-page-subtitle">Pilih titik lokasi kerja dari peta untuk validasi radius absensi.</p>
    </div>
    <a href="{{ route('admin.work-locations') }}" class="btn btn-light">Back</a>
</div>
<div class="card custom-card ka-card ka-form-card">
    <div class="card-body">
        <form method="POST" action="{{ $isEdit ? route('admin.work-locations.update', $item->id) : route('admin.work-locations.store') }}" class="row g-3">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif
            <div class="col-12">
                <div class="ka-form-section">
                    <div class="ka-form-section-title">Location Information</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input name="name" value="{{ old('name', $item?->name) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Radius Meter</label>
                            <input type="number" id="radius-meter" name="radius_meter" value="{{ old('radius_meter', $item?->radius_meter ?? 100) }}" class="form-control" min="1" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" rows="3" class="form-control">{{ old('address', $item?->address) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Map Location</label>
                            <div id="work-location-map" class="ka-location-map" data-latitude="{{ $defaultLatitude }}" data-longitude="{{ $defaultLongitude }}"></div>
                            <div class="form-text">Klik peta atau geser pin untuk menentukan koordinat lokasi kerja.</div>
                        </div>
                        <div class="col-md-6">
                            <div class="ka-coordinate-card h-100">
                                <label class="form-label">Latitude</label>
                                <input id="latitude" name="latitude" value="{{ $defaultLatitude }}" class="form-control" readonly required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ka-coordinate-card h-100">
                                <label class="form-label">Longitude</label>
                                <input id="longitude" name="longitude" value="{{ $defaultLongitude }}" class="form-control" readonly required>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="button" id="use-current-location" class="btn btn-outline-primary">Gunakan Lokasi Saya</button>
                            <span id="location-helper" class="text-muted small ms-2"></span>
                        </div>
                        <div class="col-md-3">
                            <label><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $item?->is_default))> Default</label>
                        </div>
                        <div class="col-md-3">
                            <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item?->is_active ?? true))> Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('admin.work-locations') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('assets/libs/leaflet/leaflet.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var mapElement = document.getElementById('work-location-map');
        var latitudeInput = document.getElementById('latitude');
        var longitudeInput = document.getElementById('longitude');
        var radiusInput = document.getElementById('radius-meter');
        var currentLocationButton = document.getElementById('use-current-location');
        var helper = document.getElementById('location-helper');
        var initialLatitude = parseFloat(mapElement.dataset.latitude) || -6.1753924;
        var initialLongitude = parseFloat(mapElement.dataset.longitude) || 106.8271528;
        var map = L.map(mapElement).setView([initialLatitude, initialLongitude], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([initialLatitude, initialLongitude], {
            draggable: true
        }).addTo(map);
        var radiusCircle = L.circle([initialLatitude, initialLongitude], {
            radius: Math.max(parseInt(radiusInput.value, 10) || 1, 1),
            color: '#0f4c81',
            fillColor: '#0f4c81',
            fillOpacity: 0.12,
            weight: 2
        }).addTo(map);

        function setCoordinate(latitude, longitude) {
            latitudeInput.value = Number(latitude).toFixed(7);
            longitudeInput.value = Number(longitude).toFixed(7);
            marker.setLatLng([latitude, longitude]);
            radiusCircle.setLatLng([latitude, longitude]);
            map.setView([latitude, longitude], Math.max(map.getZoom(), 16));
        }

        function setRadius() {
            radiusCircle.setRadius(Math.max(parseInt(radiusInput.value, 10) || 1, 1));
        }

        map.on('click', function (event) {
            setCoordinate(event.latlng.lat, event.latlng.lng);
        });

        marker.on('dragend', function (event) {
            var position = event.target.getLatLng();
            setCoordinate(position.lat, position.lng);
        });

        radiusInput.addEventListener('input', setRadius);
        radiusInput.addEventListener('change', setRadius);

        currentLocationButton.addEventListener('click', function () {
            if (!navigator.geolocation) {
                helper.textContent = 'Browser tidak mendukung geolocation.';
                return;
            }

            helper.textContent = 'Mengambil lokasi...';
            navigator.geolocation.getCurrentPosition(function (position) {
                setCoordinate(position.coords.latitude, position.coords.longitude);
                helper.textContent = 'Lokasi berhasil dipilih.';
            }, function () {
                helper.textContent = 'Tidak bisa mengambil lokasi. Pastikan izin GPS aktif.';
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        });
    });
</script>
@endpush
