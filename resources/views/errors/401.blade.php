@extends('errors.layout')

@section('status_code', '401')
@section('status_title', 'Akses Membutuhkan Autentikasi')
@section('status_message', 'Sesi login tidak ditemukan atau sudah tidak valid untuk membuka halaman ini.')
@section('status_hint', 'Silakan login kembali lalu ulangi akses halaman.')
