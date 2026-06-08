@extends('errors.layout')

@section('status_code', '419')
@section('status_title', 'Sesi Kedaluwarsa')
@section('status_message', 'Permintaan tidak dapat diproses karena sesi sudah berakhir.')
@section('status_hint', 'Muat ulang halaman, lalu kirim ulang data Anda.')
