<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->where('is_active', true)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        $token = $user->createToken('admin-api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->userData($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->userData($request->user())]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name'  => 'sometimes|string|max:150',
            'phone' => 'sometimes|string|max:20',
        ]);

        $request->user()->update($data);
        return response()->json(['user' => $this->userData($request->user()->fresh())]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json(['message' => 'Password lama salah.'], 422);
        }

        $request->user()->update(['password' => $request->new_password]);
        return response()->json(['message' => 'Password berhasil diubah.']);
    }

    private function userData(User $u): array
    {
        return [
            'id'    => $u->id,
            'name'  => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'role'  => $u->role,
        ];
    }
}
