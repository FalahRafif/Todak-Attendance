<?php

namespace App\Http\Controllers\Web\Hrd\Attendances;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\View\View;

class AttendanceShowController extends Controller
{
    public function __invoke(int $id, HrdService $hrdService): View
    {
        return view('pages.hrd.attendances.show', $hrdService->attendanceDetailData($id));
    }
}
