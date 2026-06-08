<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\Hr\EmployeeService;
use App\Services\Hr\HrMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminModuleController extends Controller
{
    public function __construct(
        private HrMasterService $hrMasterService,
        private EmployeeService $employeeService
    ) {
    }

    public function dashboard(): View
    {
        return view('pages.admin.dashboard', $this->hrMasterService->dashboardData());
    }

    public function departments(): View
    {
        return view('pages.admin.modules.departments.index', $this->hrMasterService->departmentPageData());
    }

    public function createDepartment(): View
    {
        return view('pages.admin.modules.departments.form', $this->hrMasterService->departmentFormData());
    }

    public function editDepartment(int $id): View
    {
        return view('pages.admin.modules.departments.form', $this->hrMasterService->departmentFormData($id));
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        $this->hrMasterService->createDepartment($this->validateDepartment($request));

        return redirect()->route('admin.departments')->with('success', 'Department berhasil dibuat.');
    }

    public function updateDepartment(Request $request, int $id): RedirectResponse
    {
        $this->hrMasterService->updateDepartment($id, $this->validateDepartment($request));

        return redirect()->route('admin.departments')->with('success', 'Department berhasil diupdate.');
    }

    public function deleteDepartment(int $id): RedirectResponse
    {
        $this->hrMasterService->deleteDepartment($id, auth()->id());

        return back()->with('success', 'Department berhasil dihapus.');
    }

    public function positions(): View
    {
        return view('pages.admin.modules.positions.index', $this->hrMasterService->positionPageData());
    }

    public function createPosition(): View
    {
        return view('pages.admin.modules.positions.form', $this->hrMasterService->positionFormData());
    }

    public function editPosition(int $id): View
    {
        return view('pages.admin.modules.positions.form', $this->hrMasterService->positionFormData($id));
    }

    public function storePosition(Request $request): RedirectResponse
    {
        $this->hrMasterService->createPosition($this->validatePosition($request));

        return redirect()->route('admin.positions')->with('success', 'Position berhasil dibuat.');
    }

    public function updatePosition(Request $request, int $id): RedirectResponse
    {
        $this->hrMasterService->updatePosition($id, $this->validatePosition($request));

        return redirect()->route('admin.positions')->with('success', 'Position berhasil diupdate.');
    }

    public function deletePosition(int $id): RedirectResponse
    {
        $this->hrMasterService->deletePosition($id, auth()->id());

        return back()->with('success', 'Position berhasil dihapus.');
    }

    public function workLocations(): View
    {
        return view('pages.admin.modules.work-locations.index', $this->hrMasterService->workLocationPageData());
    }

    public function createWorkLocation(): View
    {
        return view('pages.admin.modules.work-locations.form', $this->hrMasterService->workLocationFormData());
    }

    public function editWorkLocation(int $id): View
    {
        return view('pages.admin.modules.work-locations.form', $this->hrMasterService->workLocationFormData($id));
    }

    public function storeWorkLocation(Request $request): RedirectResponse
    {
        $this->hrMasterService->createWorkLocation($this->validateWorkLocation($request));

        return redirect()->route('admin.work-locations')->with('success', 'Lokasi kerja berhasil dibuat.');
    }

    public function updateWorkLocation(Request $request, int $id): RedirectResponse
    {
        $this->hrMasterService->updateWorkLocation($id, $this->validateWorkLocation($request));

        return redirect()->route('admin.work-locations')->with('success', 'Lokasi kerja berhasil diupdate.');
    }

    public function deleteWorkLocation(int $id): RedirectResponse
    {
        $this->hrMasterService->deleteWorkLocation($id, auth()->id());

        return back()->with('success', 'Lokasi kerja berhasil dihapus.');
    }

    public function shifts(): View
    {
        return view('pages.admin.modules.shifts.index', $this->hrMasterService->shiftPageData());
    }

    public function createShift(): View
    {
        return view('pages.admin.modules.shifts.form', $this->hrMasterService->shiftFormData());
    }

    public function editShift(int $id): View
    {
        return view('pages.admin.modules.shifts.form', $this->hrMasterService->shiftFormData($id));
    }

    public function storeShift(Request $request): RedirectResponse
    {
        $this->hrMasterService->createShift($this->validateShift($request));

        return redirect()->route('admin.shifts')->with('success', 'Shift berhasil dibuat.');
    }

    public function updateShift(Request $request, int $id): RedirectResponse
    {
        $this->hrMasterService->updateShift($id, $this->validateShift($request));

        return redirect()->route('admin.shifts')->with('success', 'Shift berhasil diupdate.');
    }

    public function deleteShift(int $id): RedirectResponse
    {
        $this->hrMasterService->deleteShift($id, auth()->id());

        return back()->with('success', 'Shift berhasil dihapus.');
    }

    public function holidays(): View
    {
        return view('pages.admin.modules.holidays.index', $this->hrMasterService->holidayPageData());
    }

    public function createHoliday(): View
    {
        return view('pages.admin.modules.holidays.form', $this->hrMasterService->holidayFormData());
    }

    public function editHoliday(int $id): View
    {
        return view('pages.admin.modules.holidays.form', $this->hrMasterService->holidayFormData($id));
    }

    public function storeHoliday(Request $request): RedirectResponse
    {
        $this->hrMasterService->createHoliday($this->validateHoliday($request));

        return redirect()->route('admin.holidays')->with('success', 'Holiday berhasil dibuat.');
    }

    public function updateHoliday(Request $request, int $id): RedirectResponse
    {
        $this->hrMasterService->updateHoliday($id, $this->validateHoliday($request));

        return redirect()->route('admin.holidays')->with('success', 'Holiday berhasil diupdate.');
    }

    public function deleteHoliday(int $id): RedirectResponse
    {
        $this->hrMasterService->deleteHoliday($id, auth()->id());

        return back()->with('success', 'Holiday berhasil dihapus.');
    }

    public function employees(): View
    {
        return view('pages.admin.modules.employees.index', $this->employeeService->pageData());
    }

    public function createEmployee(): View
    {
        return view('pages.admin.modules.employees.form', $this->employeeService->formData());
    }

    public function editEmployee(int $id): View
    {
        return view('pages.admin.modules.employees.form', $this->employeeService->formData($id));
    }

    public function storeEmployee(Request $request): RedirectResponse
    {
        $this->employeeService->createEmployeeWithUser($this->validateEmployee($request));

        return redirect()->route('admin.employees')->with('success', 'Employee dan user berhasil dibuat.');
    }

    public function updateEmployee(Request $request, int $id): RedirectResponse
    {
        $this->employeeService->updateEmployee($id, $this->validateEmployee($request, $id));

        return redirect()->route('admin.employees')->with('success', 'Employee berhasil diupdate.');
    }

    public function deleteEmployee(int $id): RedirectResponse
    {
        $this->employeeService->deleteEmployee($id, auth()->id());

        return back()->with('success', 'Employee berhasil dihapus.');
    }

    private function validateDepartment(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'parent_id' => ['nullable', 'integer', 'exists:departments,id']]);
    }

    private function validatePosition(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'department_id' => ['nullable', 'integer', 'exists:departments,id']]);
    }

    private function validateWorkLocation(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'address' => ['nullable', 'string'], 'latitude' => ['nullable', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'numeric', 'between:-180,180'], 'radius_meter' => ['required', 'integer', 'min:1'], 'is_default' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean']]);
    }

    private function validateShift(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'start_time' => ['required', 'date_format:H:i'], 'end_time' => ['required', 'date_format:H:i'], 'check_in_start_time' => ['nullable', 'date_format:H:i'], 'check_in_end_time' => ['nullable', 'date_format:H:i'], 'check_out_start_time' => ['nullable', 'date_format:H:i'], 'check_out_end_time' => ['nullable', 'date_format:H:i'], 'late_tolerance_minutes' => ['required', 'integer', 'min:0'], 'is_overnight' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean']]);
    }

    private function validateHoliday(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'holiday_date' => ['required', 'date'], 'description' => ['nullable', 'string'], 'is_national_holiday' => ['nullable', 'boolean'], 'is_company_holiday' => ['nullable', 'boolean']]);
    }

    private function validateEmployee(Request $request, ?int $employeeId = null): array
    {
        $userId = $employeeId;
        $employee = $employeeId === null ? null : $this->employeeService->formData($employeeId)['employee'];
        $employeeRoleId = $this->employeeService->formData()['employeeRoleId'];
        $employeeRequired = (int) $request->input('role_id') === (int) $employeeRoleId ? 'required' : 'nullable';

        return $request->validate(['role_id' => ['required', 'integer', 'exists:roles,id'], 'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)], 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)], 'password' => [$employeeId === null ? 'required' : 'nullable', 'string', 'min:8'], 'employee_number' => [$employeeRequired, 'string', 'max:255', Rule::unique('employees', 'employee_number')->ignore($employee?->id)], 'full_name' => [$employeeRequired, 'string', 'max:255'], 'phone' => ['nullable', 'string', 'max:30'], 'gender' => ['nullable', 'string', 'max:20'], 'employee_type_id' => ['nullable', 'integer', 'exists:references,id'], 'department_id' => ['nullable', 'integer', 'exists:departments,id'], 'position_id' => ['nullable', 'integer', 'exists:positions,id'], 'work_location_id' => ['nullable', 'integer', 'exists:work_locations,id'], 'shift_id' => ['nullable', 'integer', 'exists:shifts,id'], 'join_date' => ['nullable', 'date'], 'end_date' => ['nullable', 'date'], 'is_active' => ['nullable', 'boolean']]);
    }
}
