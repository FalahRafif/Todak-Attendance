<?php

namespace App\Http\Controllers\Web\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Services\Hr\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $employeeService) {}

    public function index(): View
    {
        return view('pages.admin.modules.employees.index', $this->employeeService->pageData());
    }

    public function create(): View
    {
        return view('pages.admin.modules.employees.form', $this->employeeService->formData());
    }

    public function edit(int $id): View
    {
        return view('pages.admin.modules.employees.form', $this->employeeService->formData($id));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->employeeService->createEmployeeWithUser($this->validated($request));

        return redirect()->route('admin.employees')->with('success', 'Employee dan user berhasil dibuat.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->employeeService->updateEmployee($id, $this->validated($request, $id));

        return redirect()->route('admin.employees')->with('success', 'Employee berhasil diupdate.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->employeeService->deleteEmployee($id, auth()->id());

        return back()->with('success', 'Employee berhasil dihapus.');
    }

    private function validated(Request $request, ?int $employeeId = null): array
    {
        $userId = $employeeId;
        $employee = $employeeId === null ? null : $this->employeeService->formData($employeeId)['employee'];
        $employeeRoleId = $this->employeeService->formData()['employeeRoleId'];
        $employeeRequired = (int) $request->input('role_id') === (int) $employeeRoleId ? 'required' : 'nullable';

        return $request->validate(['role_id' => ['required', 'integer', 'exists:roles,id'], 'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)], 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)], 'password' => [$employeeId === null ? 'required' : 'nullable', 'string', 'min:8'], 'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'employee_number' => [$employeeRequired, 'string', 'max:255', Rule::unique('employees', 'employee_number')->ignore($employee?->id)], 'full_name' => [$employeeRequired, 'string', 'max:255'], 'phone' => ['nullable', 'string', 'max:30'], 'gender' => ['nullable', 'string', 'max:20'], 'employee_type_id' => ['nullable', 'integer', 'exists:references,id'], 'department_id' => ['nullable', 'integer', 'exists:departments,id'], 'position_id' => ['nullable', 'integer', 'exists:positions,id'], 'work_location_id' => ['nullable', 'integer', 'exists:work_locations,id'], 'shift_id' => ['nullable', 'integer', 'exists:shifts,id'], 'join_date' => ['nullable', 'date'], 'end_date' => ['nullable', 'date'], 'is_active' => ['nullable', 'boolean']]);
    }
}
