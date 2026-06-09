<?php

namespace App\Http\Controllers\Web\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Services\Hr\HrMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(private HrMasterService $hrMasterService) {}

    public function index(): View
    {
        return view('pages.admin.modules.departments.index', $this->hrMasterService->departmentPageData());
    }

    public function create(): View
    {
        return view('pages.admin.modules.departments.form', $this->hrMasterService->departmentFormData());
    }

    public function edit(int $id): View
    {
        return view('pages.admin.modules.departments.form', $this->hrMasterService->departmentFormData($id));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->hrMasterService->createDepartment($request->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'parent_id' => ['nullable', 'integer', 'exists:departments,id']]));

        return redirect()->route('admin.departments')->with('success', 'Department berhasil dibuat.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->hrMasterService->updateDepartment($id, $request->validate(['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'parent_id' => ['nullable', 'integer', 'exists:departments,id']]));

        return redirect()->route('admin.departments')->with('success', 'Department berhasil diupdate.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->hrMasterService->deleteDepartment($id, auth()->id());

        return back()->with('success', 'Department berhasil dihapus.');
    }
}
