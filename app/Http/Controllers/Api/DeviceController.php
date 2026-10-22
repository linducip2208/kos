<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Registrasi token push notification (FCM) perangkat mobile penyewa.
 */
class DeviceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string|max:255']);

        $request->user()->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['message' => 'Perangkat berhasil didaftarkan.']);
    }

    public function destroy(Request $request)
    {
        $request->user()->update(['fcm_token' => null]);

        return response()->json(['message' => 'Perangkat berhasil dihapus.']);
    }
}
