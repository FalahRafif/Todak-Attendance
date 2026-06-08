<?php

namespace App\Services\Portal;

use Illuminate\View\View;
use RuntimeException;

class InternalPageService
{
    /**
     * @var array<string, array{view:string,title:string}>
     */
    private array $pages = [
        'dashboard' => ['view' => 'pages.admin.dashboard', 'title' => 'Dashboard'],
        'bookings.list' => ['view' => 'pages.admin.bookings.list', 'title' => 'Daftar Booking'],
        'bookings.detail' => ['view' => 'pages.admin.bookings.detail', 'title' => 'Booking Detail'],
        'calendar' => ['view' => 'pages.admin.calendar', 'title' => 'Calender Booking'],
        'packages' => ['view' => 'pages.admin.master.packages', 'title' => 'Packages'],
        'customers' => ['view' => 'pages.admin.customers', 'title' => 'Customers'],
        'settings' => ['view' => 'pages.admin.settings', 'title' => 'Settings'],
    ];

    public function render(string $panelPrefix, string $pageKey, array $payload = []): View
    {
        if (!isset($this->pages[$pageKey])) {
            throw new RuntimeException("Internal page config '{$pageKey}' not found.");
        }

        $page = $this->pages[$pageKey];
        $panelName = config("role_access.panel_title_by_prefix.{$panelPrefix}", ucfirst($panelPrefix));

        return view($page['view'], array_merge([
            'title' => "{$page['title']} - Etherno {$panelName}",
        ], $payload));
    }
}
