<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Occupant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('portal')->check()) {
            return redirect()->route('portal.dashboard');
        }
        return view('portal.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required',
            'password' => 'required',
        ]);

        $occupant = Occupant::where('phone', $request->phone)
            ->where('portal_active', true)->first();

        if (!$occupant || !Hash::check($request->password, $occupant->portal_password)) {
            return back()->withErrors(['phone' => 'Nomor HP atau password salah.'])->withInput();
        }

        Auth::guard('portal')->login($occupant, $request->boolean('remember'));
        $occupant->update(['portal_last_login' => now()]);

        return redirect()->route('portal.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('portal')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.login');
    }
}
