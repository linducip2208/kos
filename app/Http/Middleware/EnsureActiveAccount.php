<?php

namespace App\Http\Middleware;

use App\Models\Occupant;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabut akses token/sesi milik akun yang sudah dinonaktifkan.
 * Login sudah memblokir akun nonaktif, middleware ini menutup
 * token & sesi lama yang masih hidup setelah penonaktifan.
 */
class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && ! $user->is_active) {
            abort(403, 'Akun Anda telah dinonaktifkan.');
        }

        if ($user instanceof Occupant && ! $user->portal_active) {
            abort(403, 'Akses portal Anda telah dinonaktifkan.');
        }

        return $next($request);
    }
}
