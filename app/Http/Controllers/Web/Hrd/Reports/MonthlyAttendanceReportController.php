<?php

namespace App\Http\Controllers\Web\Hrd\Reports;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthlyAttendanceReportController extends Controller
{
    public function index(Request $request, HrdService $hrdService): View
    {
        return view('pages.hrd.reports.monthly', $hrdService->monthlyReportData($request));
    }

    public function generate(Request $request, HrdService $hrdService): RedirectResponse
    {
        $hrdService->generateMonthlyReport($request->validate(['year' => ['required', 'integer', 'min:2000'], 'month' => ['required', 'integer', 'between:1,12']]));

        return back()->with('success', 'Monthly summary berhasil digenerate.');
    }
}
