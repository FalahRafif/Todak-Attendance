@extends('layouts.guest.guest')

@section('content')
    @include('pages.public.landing-page.sections.hero')
    @include('pages.public.landing-page.sections.packages')
    @include('pages.public.landing-page.sections.portfolio')
    @include('pages.public.landing-page.sections.testimonials')
    @include('pages.public.landing-page.sections.faq')
    @include('pages.public.landing-page.sections.cta')
@endsection

