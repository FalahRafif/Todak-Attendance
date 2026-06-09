<?php

namespace App\Http\Controllers\Web\Employee;

use App\Http\Controllers\Controller;
use App\Services\Employee\EmployeePortalService;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __invoke(EmployeePortalService $employeePortalService): View
    {
        return view('pages.employee.profile', $employeePortalService->profileData());
    }
}
