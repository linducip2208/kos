<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Notifikasi in-app untuk penyewa (guard portal via Sanctum Occupant).
 * Scope otomatis ke notifikasi milik sendiri via relasi user.
 */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->notifications()->latest()->paginate($request->integer('per_page', 20))
        );
    }

    public function unreadCount(Request $request)
    {
        return response()->json(['count' => $request->user()->unreadNotifications()->count()]);
    }

    public function markRead(Request $request, string $id)
    {
        $request->user()->notifications()->findOrFail($id)->markAsRead();

        return response()->json(['message' => 'Notifikasi ditandai dibaca.']);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'Semua notifikasi ditandai dibaca.']);
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return response()->json(['message' => 'Notifikasi dihapus.']);
    }
}
