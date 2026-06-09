<?php

namespace App\Http\Controllers\Web\Hrd\Attendances;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LateAttendanceController extends Controller
{
    public function __invoke(Request $request, HrdService $hrdService): View
    {
        $query = $hrdService->attendanceBaseQuery()->where('late_minutes', '>', 0);

        return view('pages.hrd.attendances.index', $hrdService->attendancePageData($request, 'Terlambat', $query));
    }
}
