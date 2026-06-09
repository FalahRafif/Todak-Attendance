<?php

namespace App\Http\Controllers\Web\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Services\Hr\HrMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function __construct(private HrMasterService $hrMasterService) {}

    public function index(): View
    {
        return view('pages.admin.modules.holidays.index', $this->hrMasterService->holidayPageData());
    }

    public function create(): View
    {
        return view('pages.admin.modules.holidays.form', $this->hrMasterService->holidayFormData());
    }

    public function edit(int $id): View
    {
        return view('pages.admin.modules.holidays.form', $this->hrMasterService->holidayFormData($id));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->hrMasterService->createHoliday($this->validated($request));

        return redirect()->route('admin.holidays')->with('success', 'Holiday berhasil dibuat.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->hrMasterService->updateHoliday($id, $this->validated($request));

        return redirect()->route('admin.holidays')->with('success', 'Holiday berhasil diupdate.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->hrMasterService->deleteHoliday($id, auth()->id());

        return back()->with('success', 'Holiday berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'holiday_date' => ['required', 'date'], 'description' => ['nullable', 'string'], 'is_national_holiday' => ['nullable', 'boolean'], 'is_company_holiday' => ['nullable', 'boolean']]);
    }
}
