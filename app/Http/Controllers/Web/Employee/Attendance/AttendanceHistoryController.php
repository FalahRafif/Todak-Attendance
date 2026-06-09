<?php

namespace App\Http\Controllers\Web\Employee\Attendance;

use App\Http\Controllers\Controller;
use App\Services\Employee\EmployeePortalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceHistoryController extends Controller
{
    public function index(Request $request, EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.attendance.history', $employeePortalService->historyData($request));
    }

    public function show(int $id, EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.attendance.show', $employeePortalService->attendanceDetailData($id));
    }
}
