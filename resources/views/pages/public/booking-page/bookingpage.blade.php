@extends('layouts.guest.guest')

@section('content')
    <section class="section-block container booking-section" id="booking">
        @php
            $activeBookingTab = $errors->any() ? 'form' : 'flow';
        @endphp

        <div class="section-heading booking-heading">
            <p class="eyebrow">Booking</p>
            <h2>Mulai dari alur proses, lalu isi form booking</h2>
            <p class="section-lead">Baca alur proses dulu supaya jelas kapan slot terkunci dan kapan DP dibutuhkan. Setelah itu lanjut ke form booking untuk mengirim request.</p>
        </div>

        <div class="booking-tabs" data-booking-tabs>
            <div class="booking-tab-list" role="tablist" aria-label="Booking tabs">
                <button class="booking-tab @if($activeBookingTab === 'flow') is-active @endif" type="button" role="tab" aria-selected="{{ $activeBookingTab === 'flow' ? 'true' : 'false' }}" aria-controls="booking_tab_flow" id="booking_tab_flow_button" data-booking-tab="flow">Alur Proses Booking</button>
                <button class="booking-tab @if($activeBookingTab === 'form') is-active @endif" type="button" role="tab" aria-selected="{{ $activeBookingTab === 'form' ? 'true' : 'false' }}" aria-controls="booking_tab_form" id="booking_tab_form_button" data-booking-tab="form">Form Booking</button>
            </div>

            <div class="booking-tab-panels">
                <div class="booking-tab-panel @if($activeBookingTab === 'flow') is-active @endif" role="tabpanel" id="booking_tab_flow" aria-labelledby="booking_tab_flow_button" data-booking-panel="flow" @if($activeBookingTab !== 'flow') hidden @endif>
                    @include('pages.public.booking-page.sections.booking-flow')
                </div>
                <div class="booking-tab-panel @if($activeBookingTab === 'form') is-active @endif" role="tabpanel" id="booking_tab_form" aria-labelledby="booking_tab_form_button" data-booking-panel="form" @if($activeBookingTab !== 'form') hidden @endif>
                    @include('pages.public.booking-page.sections.booking-form')
                    @include('pages.public.booking-page.sections.support-links')
                </div>
            </div>
        </div>
    </section>
@endsection
