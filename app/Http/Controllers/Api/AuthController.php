<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Occupant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required',
            'password' => 'required',
        ]);

        $occupant = Occupant::where('phone', $request->phone)
            ->where('portal_active', true)->first();

        if (!$occupant || !Hash::check($request->password, $occupant->portal_password)) {
            return response()->json(['message' => 'Nomor HP atau password salah.'], 401);
        }

        $token = $occupant->createToken('mobile')->plainTextToken;
        $occupant->update(['portal_last_login' => now()]);

        return response()->json([
            'token'    => $token,
            'occupant' => $this->occupantData($occupant),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        return response()->json(['occupant' => $this->occupantData($request->user())]);
    }

    public function updateProfile(Request $request)
    {
        $occupant = $request->user();
        $request->validate([
            'name'     => 'required|max:255',
            'phone'    => 'required|max:20|unique:occupants,phone,'.$occupant->id,
            'email'    => 'nullable|email|max:255',
            'whatsapp' => 'nullable|max:20',
        ]);

        $occupant->update($request->only('name', 'phone', 'email', 'whatsapp'));

        return response()->json([
            'message'  => 'Profil berhasil diperbarui.',
            'occupant' => $this->occupantData($occupant->fresh()),
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        $occupant = $request->user();

        if (!Hash::check($request->current_password, $occupant->portal_password)) {
            return response()->json(['message' => 'Password saat ini tidak cocok.'], 422);
        }

        $occupant->setPortalPassword($request->password);
        $occupant->save();

        return response()->json(['message' => 'Password berhasil diubah.']);
    }

    private function occupantData(Occupant $o): array
    {
        return [
            'id'       => $o->id,
            'name'     => $o->name,
            'phone'    => $o->phone,
            'email'    => $o->email,
            'whatsapp' => $o->whatsapp,
        ];
    }
}
