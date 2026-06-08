@extends('errors.layout')

@section('status_code', '429')
@section('status_title', 'Terlalu Banyak Permintaan')
@section('status_message', 'Aktivitas Anda melebihi batas sementara yang diizinkan sistem.')
@section('status_hint', 'Tunggu beberapa saat, lalu coba lagi secara bertahap.')
