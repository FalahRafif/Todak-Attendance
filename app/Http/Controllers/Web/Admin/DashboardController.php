<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\Hr\HrMasterService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(HrMasterService $hrMasterService): View
    {
        return view('pages.admin.dashboard', $hrMasterService->dashboardData());
    }
}
