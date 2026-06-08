<?php

use App\Enums\RoleName;

return [
    'roles' => [
        'internal' => [RoleName::Admin->value, RoleName::Hrd->value],
        'admin_only' => [RoleName::Admin->value],
    ],

    'route_prefix_by_role' => [
        RoleName::Admin->value => 'admin',
        RoleName::Hrd->value => 'hrd',
    ],

    'dashboard_route_by_role' => [
        RoleName::Admin->value => 'admin.dashboard',
        RoleName::Hrd->value => 'hrd.dashboard',
    ],

    'panel_title_by_prefix' => [
        'admin' => 'Admin',
        'hrd' => 'HRD',
    ],

    'guest' => [
        'menu' => [],
        'cta' => null,
    ],

    'menu' => [
        [
            'section' => 'Overview',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route_name' => 'dashboard',
                    'active' => ['dashboard'],
                    'roles' => [RoleName::Admin->value, RoleName::Hrd->value],
                ],
            ],
        ],
    ],
];
