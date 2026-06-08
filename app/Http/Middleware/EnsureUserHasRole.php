<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function __construct(private AuthService $authService)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if (!$user instanceof User) {
            abort(403);
        }

        if (empty($roles)) {
            if ($this->authService->isInternalUser($user)) {
                $this->authService->syncInternalSession($request, $user->loadMissing('role'));
            }

            return $next($request);
        }

        if (!$user->hasRole($roles)) {
            abort(403);
        }

        if ($this->authService->isInternalUser($user)) {
            $this->authService->syncInternalSession($request, $user->loadMissing('role'));
        }

        return $next($request);
    }
}
