<?php

namespace App\Http\Controllers\Web\Employee\Requests;

use App\Http\Controllers\Controller;
use App\Services\Employee\EmployeePortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AttendanceCorrectionController extends Controller
{
    public function index(Request $request, EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.attendance-corrections.index', $employeePortalService->correctionListData($request));
    }

    public function create(EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.attendance-corrections.form', $employeePortalService->correctionFormData());
    }

    public function store(Request $request, EmployeePortalService $employeePortalService): RedirectResponse
    {
        $employeePortalService->createCorrection($request->validate(['correction_date' => ['required', 'date'], 'requested_check_in_at' => ['nullable', 'date'], 'requested_check_out_at' => ['nullable', 'date'], 'reason' => ['required', 'string']]));

        return redirect()->route('employee.attendance-corrections')->with('success', 'Attendance correction berhasil dibuat.');
    }

    public function show(int $id, EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.attendance-corrections.show', $employeePortalService->correctionDetailData($id));
    }

    public function cancel(int $id, EmployeePortalService $employeePortalService): RedirectResponse
    {
        try {
            $employeePortalService->cancelCorrection($id);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('employee.attendance-corrections')->with('success', 'Attendance correction berhasil dibatalkan.');
    }
}
