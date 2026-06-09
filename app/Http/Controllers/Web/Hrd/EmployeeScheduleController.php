<?php

namespace App\Http\Controllers\Web\Hrd;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\View\View;

class EmployeeScheduleController extends Controller
{
    public function __invoke(HrdService $hrdService): View
    {
        return view('pages.hrd.employee-schedules', $hrdService->scheduleListData());
    }
}
