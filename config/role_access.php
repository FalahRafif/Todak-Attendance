<?php

use App\Enums\RoleName;

return [
    'roles' => [
        'internal' => [RoleName::Admin->value, RoleName::Hrd->value, RoleName::Employee->value],
        'admin_only' => [RoleName::Admin->value],
    ],

    'route_prefix_by_role' => [
        RoleName::Admin->value => 'admin',
        RoleName::Hrd->value => 'hrd',
        RoleName::Employee->value => 'employee',
    ],

    'dashboard_route_by_role' => [
        RoleName::Admin->value => 'admin.dashboard',
        RoleName::Hrd->value => 'hrd.dashboard',
        RoleName::Employee->value => 'employee.dashboard',
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
        [
            'section' => 'Monitoring Absensi',
            'items' => [
                ['label' => 'Absensi Harian', 'route_name' => 'attendances', 'active' => ['attendances'], 'roles' => [RoleName::Hrd->value]],
                ['label' => 'Belum Check-in', 'route_name' => 'attendances.not-checked-in', 'active' => ['attendances.not-checked-in'], 'roles' => [RoleName::Hrd->value]],
                ['label' => 'Belum Check-out', 'route_name' => 'attendances.incomplete', 'active' => ['attendances.incomplete'], 'roles' => [RoleName::Hrd->value]],
                ['label' => 'Terlambat', 'route_name' => 'attendances.late', 'active' => ['attendances.late'], 'roles' => [RoleName::Hrd->value]],
                ['label' => 'Outside Radius', 'route_name' => 'attendances.outside-radius', 'active' => ['attendances.outside-radius'], 'roles' => [RoleName::Hrd->value]],
            ],
        ],
        [
            'section' => 'Approval',
            'items' => [
                ['label' => 'Leave Requests', 'route_name' => 'leave-requests', 'active' => ['leave-requests*'], 'roles' => [RoleName::Hrd->value]],
                ['label' => 'Attendance Corrections', 'route_name' => 'attendance-corrections', 'active' => ['attendance-corrections*'], 'roles' => [RoleName::Hrd->value]],
            ],
        ],
        [
            'section' => 'Reports',
            'items' => [
                ['label' => 'Daily Attendance', 'route_name' => 'reports.daily-attendance', 'active' => ['reports.daily-attendance*'], 'roles' => [RoleName::Hrd->value]],
                ['label' => 'Monthly Attendance', 'route_name' => 'reports.monthly-attendance', 'active' => ['reports.monthly-attendance*'], 'roles' => [RoleName::Hrd->value]],
            ],
        ],
        [
            'section' => 'HR Data',
            'items' => [
                ['label' => 'Employee Schedules', 'route_name' => 'employee-schedules', 'active' => ['employee-schedules*'], 'roles' => [RoleName::Hrd->value]],
                ['label' => 'Leave Balances', 'route_name' => 'leave-balances', 'active' => ['leave-balances*'], 'roles' => [RoleName::Hrd->value]],
            ],
        ],
        [
            'section' => 'Employee Portal',
            'items' => [
                ['label' => 'Attendance Today', 'route_name' => 'attendance', 'active' => ['attendance'], 'roles' => [RoleName::Employee->value]],
                ['label' => 'Attendance History', 'route_name' => 'attendance.history', 'active' => ['attendance.history*'], 'roles' => [RoleName::Employee->value]],
                ['label' => 'Leave Requests', 'route_name' => 'leave-requests', 'active' => ['leave-requests*'], 'roles' => [RoleName::Employee->value]],
                ['label' => 'Attendance Corrections', 'route_name' => 'attendance-corrections', 'active' => ['attendance-corrections*'], 'roles' => [RoleName::Employee->value]],
                ['label' => 'Profile', 'route_name' => 'profile', 'active' => ['profile'], 'roles' => [RoleName::Employee->value]],
            ],
        ],
    ],
];
