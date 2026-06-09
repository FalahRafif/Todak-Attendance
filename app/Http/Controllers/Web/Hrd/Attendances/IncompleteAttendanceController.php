<?php

namespace App\Http\Controllers\Web\Hrd\Attendances;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncompleteAttendanceController extends Controller
{
    public function __invoke(Request $request, HrdService $hrdService): View
    {
        $query = $hrdService->attendanceBaseQuery()->whereNotNull('check_in_at')->whereNull('check_out_at');

        return view('pages.hrd.attendances.index', $hrdService->attendancePageData($request, 'Belum Check-out', $query));
    }
}
