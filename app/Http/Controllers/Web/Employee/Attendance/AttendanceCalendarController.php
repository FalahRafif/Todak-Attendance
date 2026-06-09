<?php

namespace App\Http\Controllers\Web\Employee\Attendance;

use App\Http\Controllers\Controller;
use App\Services\Employee\EmployeePortalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceCalendarController extends Controller
{
    public function __invoke(Request $request, EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.attendance.calendar', $employeePortalService->calendarData($request));
    }
}
