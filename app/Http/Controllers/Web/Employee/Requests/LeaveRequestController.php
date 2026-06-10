<?php

namespace App\Http\Controllers\Web\Employee\Requests;

use App\Http\Controllers\Controller;
use App\Services\Employee\EmployeePortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class LeaveRequestController extends Controller
{
    public function index(Request $request, EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.leave-requests.index', $employeePortalService->leaveListData($request));
    }

    public function create(EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.leave-requests.form', $employeePortalService->leaveFormData());
    }

    public function store(Request $request, EmployeePortalService $employeePortalService): RedirectResponse
    {
        $payload = $request->validate(['leave_type_id' => ['required', 'integer', 'exists:references,id'], 'start_date' => ['required', 'date'], 'end_date' => ['required', 'date', 'after_or_equal:start_date'], 'reason' => ['required', 'string'], 'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120']]);
        try {
            $employeePortalService->createLeave($payload);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('employee.leave-requests')->with('success', 'Leave request berhasil dibuat.');
    }

    public function show(int $id, EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.leave-requests.show', $employeePortalService->leaveDetailData($id));
    }

    public function cancel(int $id, EmployeePortalService $employeePortalService): RedirectResponse
    {
        try {
            $employeePortalService->cancelLeave($id);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('employee.leave-requests')->with('success', 'Leave request berhasil dibatalkan.');
    }
}
