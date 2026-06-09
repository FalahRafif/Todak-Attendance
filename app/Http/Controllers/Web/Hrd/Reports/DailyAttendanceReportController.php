<?php

namespace App\Http\Controllers\Web\Hrd\Reports;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyAttendanceReportController extends Controller
{
    public function __invoke(Request $request, HrdService $hrdService): View
    {
        return view('pages.hrd.reports.daily', $hrdService->attendancePageData($request, 'Daily Attendance Report'));
    }
}
