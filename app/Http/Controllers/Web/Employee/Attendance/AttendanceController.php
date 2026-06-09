<?php

namespace App\Http\Controllers\Web\Employee\Attendance;

use App\Http\Controllers\Controller;
use App\Services\Employee\EmployeePortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AttendanceController extends Controller
{
    public function today(EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.attendance.today', $employeePortalService->attendanceData());
    }

    public function checkInForm(EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.attendance.form', $employeePortalService->attendanceFormData('check_in'));
    }

    public function checkOutForm(EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.attendance.form', $employeePortalService->attendanceFormData('check_out'));
    }

    public function checkIn(Request $request, EmployeePortalService $employeePortalService): RedirectResponse
    {
        $payload = $request->validate(['photo_data' => ['required', 'string'], 'latitude' => ['required', 'numeric', 'between:-90,90'], 'longitude' => ['required', 'numeric', 'between:-180,180'], 'gps_accuracy_meter' => ['nullable', 'numeric', 'min:0'], 'work_mode_id' => ['nullable', 'integer', 'exists:references,id'], 'note' => ['nullable', 'string']]);
        try {
            $employeePortalService->checkIn($payload, $request);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('employee.attendance')->with('success', 'Check-in berhasil.');
    }

    public function checkOut(Request $request, EmployeePortalService $employeePortalService): RedirectResponse
    {
        $payload = $request->validate(['photo_data' => ['required', 'string'], 'latitude' => ['required', 'numeric', 'between:-90,90'], 'longitude' => ['required', 'numeric', 'between:-180,180'], 'gps_accuracy_meter' => ['nullable', 'numeric', 'min:0'], 'work_mode_id' => ['nullable', 'integer', 'exists:references,id'], 'note' => ['nullable', 'string']]);
        try {
            $employeePortalService->checkOut($payload, $request);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('employee.attendance')->with('success', 'Check-out berhasil.');
    }
}
