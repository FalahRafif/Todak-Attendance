<?php

namespace App\Services\Portal;

use Illuminate\View\View;
use RuntimeException;

class GuestPageService
{
    /**
     * @var array<string, array{view:string,title:string}>
     */
    private array $pages = [
        'landing' => ['view' => 'pages.public.landing-page.landingpage', 'title' => 'Etherno - Wedding Photography'],
        'packages.page' => ['view' => 'pages.public.packages-page.index', 'title' => 'Semua Paket - Etherno'],
        'about.etherno' => ['view' => 'pages.public.about-page.etherno', 'title' => 'Tentang Etherno'],
        'booking.form' => ['view' => 'pages.public.booking-page.bookingpage', 'title' => 'Form Booking - Etherno'],
        'booking.success' => ['view' => 'pages.public.booking-page.support.success', 'title' => 'Request Booking Terkirim - Etherno'],
        'booking.status' => ['view' => 'pages.public.booking-page.support.status', 'title' => 'Cek Status Booking - Etherno'],
        'booking.reschedule' => ['view' => 'pages.public.booking-page.support.reschedule', 'title' => 'Request Reschedule - Etherno'],
        'booking.cancellation.policy' => ['view' => 'pages.public.booking-page.support.cancellation-policy', 'title' => 'Kebijakan Booking - Etherno'],
    ];

    public function render(string $pageKey, array $payload = []): View
    {
        if (!isset($this->pages[$pageKey])) {
            throw new RuntimeException("Guest page config '{$pageKey}' not found.");
        }

        $page = $this->pages[$pageKey];

        return view($page['view'], array_merge([
            'title' => $page['title'],
        ], $payload));
    }
}
