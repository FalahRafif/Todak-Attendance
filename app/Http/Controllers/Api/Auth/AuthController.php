<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if ($this->authService->attempt($request->email(), $request->password(), $request->remember())) {
            $request->session()->regenerate();
            $user = $request->user()?->loadMissing('role');

            if (!$user instanceof User || !$this->authService->isInternalUser($user)) {
                $this->authService->clearInternalSession($request);
                $this->authService->logout();

                return back()
                    ->withErrors(['email' => 'Akun ini belum memiliki akses ke panel internal.'])
                    ->withInput($request->only('email'));
            }

            $this->authService->syncInternalSession($request, $user);
            $dashboardRoute = $this->authService->resolveDashboardRoute($user) ?? 'admin.dashboard';

            return redirect()->route($dashboardRoute);
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->withInput($request->only('email'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->clearInternalSession($request);
        $this->authService->logout();

        return redirect()->route('login');
    }
}
