<?php

namespace App\Http\Controllers\Web\Hrd;

use App\Http\Controllers\Controller;
use App\Services\Hrd\HrdService;
use Illuminate\View\View;

class LeaveBalanceController extends Controller
{
    public function __invoke(HrdService $hrdService): View
    {
        return view('pages.hrd.leave-balances', $hrdService->leaveBalanceListData());
    }
}
