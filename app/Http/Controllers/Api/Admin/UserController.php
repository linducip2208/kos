<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::select('id', 'name', 'email', 'phone', 'role', 'is_active', 'created_at')->get());
    }

    public function store(Request $request)
    {
        $this->ownerOnly($request);

        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'     => 'required|in:staff,viewer',
            'phone'    => 'nullable|string|max:20',
        ]);

        $user = User::create($data);
        return response()->json($user->only(['id', 'name', 'email', 'role', 'phone']), 201);
    }

    public function update(Request $request, User $user)
    {
        $this->ownerOnly($request);

        $data = $request->validate([
            'name'      => 'sometimes|string|max:150',
            'phone'     => 'nullable|string|max:20',
            'role'      => 'in:staff,viewer',
            'is_active' => 'boolean',
        ]);

        $user->update($data);
        return response()->json($user->only(['id', 'name', 'email', 'role', 'phone', 'is_active']));
    }

    public function destroy(Request $request, User $user)
    {
        $this->ownerOnly($request);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Tidak bisa hapus akun sendiri.'], 422);
        }

        $user->delete();
        return response()->json(['message' => 'User dihapus.']);
    }

    private function ownerOnly(Request $request): void
    {
        if (!$request->user()->isOwner()) {
            abort(403, 'Hanya owner yang dapat mengelola user.');
        }
    }
}
