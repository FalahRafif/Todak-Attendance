<?php

namespace App\Http\Controllers\Web\Hrd;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityCalendarController extends Controller
{
    public function __invoke(Request $request, HrdService $hrdService): View
    {
        return view('pages.hrd.activity-calendar', $hrdService->activityCalendarData($request));
    }
}
