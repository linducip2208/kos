<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('portal')->check()) {
            return redirect()->route('portal.login');
        }

        // Cabut sesi bila akses portal dinonaktifkan setelah login.
        if (!Auth::guard('portal')->user()?->portal_active) {
            Auth::guard('portal')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('portal.login')
                ->withErrors(['phone' => 'Akses portal Anda telah dinonaktifkan.']);
        }

        return $next($request);
    }
}
