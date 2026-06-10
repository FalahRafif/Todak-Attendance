<?php

namespace App\Http\Controllers\Web\Hrd;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeScheduleController extends Controller
{
    public function index(Request $request, HrdService $hrdService): View
    {
        return view('pages.hrd.employee-schedules', $hrdService->scheduleListData($request));
    }

    public function store(Request $request, HrdService $hrdService): RedirectResponse
    {
        $payload = $request->validate(['employee_id' => ['required', 'integer', 'exists:employees,id'], 'schedule_date' => ['required', 'date'], 'shift_id' => ['nullable', 'integer', 'exists:shifts,id'], 'is_day_off' => ['nullable', 'boolean'], 'note' => ['nullable', 'string', 'max:255']]);
        $hrdService->saveEmployeeSchedule($payload);

        return back()->with('success', 'Jadwal karyawan berhasil disimpan.');
    }

    public function destroy(int $id, HrdService $hrdService): RedirectResponse
    {
        $hrdService->deleteEmployeeSchedule($id);

        return back()->with('success', 'Jadwal karyawan berhasil dihapus.');
    }
}
