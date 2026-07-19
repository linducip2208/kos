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
