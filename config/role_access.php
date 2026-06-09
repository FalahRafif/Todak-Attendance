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
                ['label' => 'Dashboard', 'route_name' => 'dashboard', 'active' => ['dashboard'], 'roles' => [RoleName::Admin->value, RoleName::Hrd->value]],
            ],
        ],
        [
            'section' => 'Master Data',
            'items' => [
                ['label' => 'Departments', 'route_name' => 'departments', 'active' => ['departments*'], 'roles' => [RoleName::Admin->value]],
                ['label' => 'Positions', 'route_name' => 'positions', 'active' => ['positions*'], 'roles' => [RoleName::Admin->value]],
                ['label' => 'Work Locations', 'route_name' => 'work-locations', 'active' => ['work-locations*'], 'roles' => [RoleName::Admin->value]],
                ['label' => 'Shifts', 'route_name' => 'shifts', 'active' => ['shifts*'], 'roles' => [RoleName::Admin->value]],
                ['label' => 'Holidays', 'route_name' => 'holidays', 'active' => ['holidays*'], 'roles' => [RoleName::Admin->value]],
            ],
        ],
        [
            'section' => 'Employee Management',
            'items' => [
                ['label' => 'Employees', 'route_name' => 'employees', 'active' => ['employees*'], 'roles' => [RoleName::Admin->value]],
            ],
        ],
    ],
];
