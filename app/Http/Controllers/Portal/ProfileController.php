<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $occupant = Auth::guard('portal')->user();
        return view('portal.profile.edit', compact('occupant'));
    }

    public function update(Request $request)
    {
        $occupant = Auth::guard('portal')->user();
        $request->validate([
            'name'     => 'required|max:255',
            'phone'    => 'required|max:20',
            'email'    => 'nullable|email|max:255',
            'whatsapp' => 'nullable|max:20',
        ]);

        $occupant->update($request->only('name', 'phone', 'email', 'whatsapp'));

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        $occupant = Auth::guard('portal')->user();

        if (!Hash::check($request->current_password, $occupant->portal_password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak cocok.']);
        }

        $occupant->setPortalPassword($request->password);
        $occupant->save();

        return back()->with('success', 'Password berhasil diubah.');
    }
}
