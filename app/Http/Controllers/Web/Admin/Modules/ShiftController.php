<?php

namespace App\Http\Controllers\Web\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Services\Hr\HrMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(private HrMasterService $hrMasterService) {}

    public function index(): View
    {
        return view('pages.admin.modules.shifts.index', $this->hrMasterService->shiftPageData());
    }

    public function create(): View
    {
        return view('pages.admin.modules.shifts.form', $this->hrMasterService->shiftFormData());
    }

    public function edit(int $id): View
    {
        return view('pages.admin.modules.shifts.form', $this->hrMasterService->shiftFormData($id));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->hrMasterService->createShift($this->validated($request));

        return redirect()->route('admin.shifts')->with('success', 'Shift berhasil dibuat.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->hrMasterService->updateShift($id, $this->validated($request));

        return redirect()->route('admin.shifts')->with('success', 'Shift berhasil diupdate.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->hrMasterService->deleteShift($id, auth()->id());

        return back()->with('success', 'Shift berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'start_time' => ['required', 'date_format:H:i'], 'end_time' => ['required', 'date_format:H:i'], 'check_in_start_time' => ['nullable', 'date_format:H:i'], 'check_in_end_time' => ['nullable', 'date_format:H:i'], 'check_out_start_time' => ['nullable', 'date_format:H:i'], 'check_out_end_time' => ['nullable', 'date_format:H:i'], 'late_tolerance_minutes' => ['required', 'integer', 'min:0'], 'is_overnight' => ['nullable', 'boolean'], 'applicable_days' => ['required', 'string', 'in:weekday,weekend,all,custom'], 'custom_days' => ['nullable', 'array'], 'custom_days.*' => ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'], 'is_active' => ['nullable', 'boolean']]);
    }
}
