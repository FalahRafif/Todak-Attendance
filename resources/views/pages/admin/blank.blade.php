@extends('layouts.admin.admin')

@section('title', $title ?? 'Dashboard — KlikAbsen')

@section('content')
<div style="padding: 32px 0;">
    <div style="background: linear-gradient(135deg, #0f4c81, #0b2742); color: #fff; border-radius: 24px; padding: 32px; box-shadow: 0 18px 40px rgba(15, 76, 129, .18);">
        <div style="font-size: 13px; letter-spacing: .12em; text-transform: uppercase; opacity: .72; font-weight: 800;">KlikAbsen</div>
        <h1 style="margin: 10px 0 8px; font-size: 34px; font-weight: 900; letter-spacing: -.04em;">Dashboard Absensi</h1>
        <p style="margin: 0; max-width: 620px; color: rgba(255,255,255,.72); line-height: 1.7;">Panel awal untuk monitoring absensi, employee management, shift, lokasi kerja, cuti, izin, dan approval HRD.</p>
    </div>
</div>
@endsection
