@extends('errors.layout')

@section('status_code', '422')
@section('status_title', 'Permintaan Tidak Valid')
@section('status_message', 'Data yang dikirim belum sesuai format yang dibutuhkan server.')
@section('status_hint', 'Periksa input, lalu kirim ulang formulir Anda.')
