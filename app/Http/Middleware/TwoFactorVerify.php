<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;

class TwoFactorVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        switch ($guard) {
            case 'admin':
                $sessionCookie = "admin_2fa";
                $route = route('admin.2fa.verify');
                break;
            case 'reviewer':
                $sessionCookie = "reviewer_2fa";
                $route = route('reviewer.2fa.verify');
                break;
            default:
                $sessionCookie = "user_2fa";
                $route = route('2fa.verify');
        }

        $authGuard = $guard ?: 'web';
        $user = Auth::guard($guard)->user();
        $marker = $request->session()->get($sessionCookie);
        $verified = is_array($marker)
            && ($marker['guard'] ?? null) === $authGuard
            && (int) ($marker['user_id'] ?? 0) === (int) ($user?->getAuthIdentifier() ?? 0)
            && ($marker['session_id'] ?? null) === $request->session()->getId();

        if ($user && $user->google2fa_status && !$verified) {
            return redirect($route);
        }

        return $next($request);
    }
}
