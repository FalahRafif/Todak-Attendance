<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function showLogin(): View|RedirectResponse
    {
        if (auth()->check()) {
            $request = request();
            $user = $request->user()?->loadMissing('role');

            if ($user instanceof User && $this->authService->isInternalUser($user)) {
                $this->authService->syncInternalSession($request, $user);
                $dashboardRoute = $this->authService->resolveDashboardRoute($user);

                if (is_string($dashboardRoute)) {
                    return redirect()->route($dashboardRoute);
                }
            }

            $this->authService->clearInternalSession($request);
            $this->authService->logout();
        }

        return view('pages.auth.login', ['title' => 'Login - Todak Attendace Admin']);
    }
}
