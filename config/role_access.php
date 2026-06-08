<?php

use App\Enums\RoleName;

return [
    'roles' => [
        'internal' => [RoleName::Admin->value, RoleName::Petugas->value],
        'admin_only' => [RoleName::Admin->value],
    ],

    'route_prefix_by_role' => [
        RoleName::Admin->value => 'admin',
        RoleName::Petugas->value => 'petugas',
    ],

    'dashboard_route_by_role' => [
        RoleName::Admin->value => 'admin.dashboard',
        RoleName::Petugas->value => 'petugas.dashboard',
    ],

    'panel_title_by_prefix' => [
        'admin' => 'Admin',
        'petugas' => 'Petugas',
    ],

    'guest' => [
        'menu' => [
            [
                'section' => 'Main',
                'items' => [
                    [
                        'label' => 'Tentang Kami',
                        'route' => 'about.etherno',
                    ],
                    [
                        'label' => 'Portofolio',
                        'route' => 'home',
                        'fragment' => 'portfolio',
                    ],
                    [
                        'label' => 'Paket',
                        'route' => 'home',
                        'fragment' => 'packages',
                    ],
                    [
                        'label' => 'FAQ',
                        'route' => 'home',
                        'fragment' => 'faq',
                    ],
                    [
                        'type' => 'dropdown',
                        'label' => 'Info Booking',
                        'items' => [
                            [
                                'label' => 'Alur Proses Booking',
                                'route' => 'booking.page',
                            ],
                            [
                                'label' => 'Kebijakan Booking',
                                'route' => 'booking.cancellation.policy',
                            ],
                            [
                                'label' => 'Detail Booking Anda',
                                'route' => 'booking.status',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'cta' => [
            'label' => 'Booking Sekarang',
            'route' => 'booking.page',
            'aria_label' => 'Booking Sekarang',
        ],
    ],

    'menu' => [
        [
            'section' => 'Overview',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route_name' => 'dashboard',
                    'active' => ['dashboard'],
                    'roles' => ['Admin', 'Petugas'],
                ],
            ],
        ],
        [
            'section' => 'Menu Utama',
            'items' => [
                [
                    'label' => 'Daftar Booking',
                    'route_name' => 'bookings.list',
                    'active' => ['bookings.list', 'bookings.detail'],
                    'roles' => ['Admin', 'Petugas'],
                ],
                [
                    'label' => 'Calender Booking',
                    'route_name' => 'calendar',
                    'active' => ['calendar'],
                    'roles' => ['Admin', 'Petugas'],
                ],
            ],
        ],
        [
            'section' => 'Aturan',
            'items' => [
                [
                    'label' => 'Aturan Harga Lokasi',
                    'route_name' => 'location.rules',
                    'active' => ['location.rules', 'location.rules.create', 'location.rules.edit'],
                    'roles' => ['Admin'],
                ],
                [
                    'label' => 'Aturan Waktu Pembayaran',
                    'route_name' => 'payment-date-rules',
                    'active' => ['payment-date-rules', 'payment-date-rules.create', 'payment-date-rules.edit'],
                    'roles' => ['Admin'],
                ],
                [
                    'label' => 'Aturan Persen DP',
                    'route_name' => 'dp-percentage-rules',
                    'active' => ['dp-percentage-rules', 'dp-percentage-rules.create', 'dp-percentage-rules.edit'],
                    'roles' => ['Admin'],
                ],
                [
                    'label' => 'Aturan Paket',
                    'route_name' => 'package-date-rules',
                    'active' => ['package-date-rules', 'package-date-rules.create', 'package-date-rules.edit'],
                    'roles' => ['Admin'],
                ],
            ],
        ],
        [
            'section' => 'Management Data',
            'items' => [
                [
                    'label' => 'Management Paket',
                    'route_name' => 'packages',
                    'active' => ['packages', 'packages.create', 'packages.edit'],
                    'roles' => ['Admin'],
                ],
                [
                    'label' => 'Management User/Akun',
                    'route_name' => 'users',
                    'active' => ['users', 'users.create', 'users.edit'],
                    'roles' => ['Admin'],
                ],
            ],
        ],
    ],
];
