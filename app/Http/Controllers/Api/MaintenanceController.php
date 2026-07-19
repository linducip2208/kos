<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Notifications\MaintenanceSubmittedNotification;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $requests = $request->user()->maintenanceRequests()
            ->with('room')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $requests->map(fn ($r) => [
                'id'          => $r->id,
                'title'       => $r->title,
                'description' => $r->description,
                'priority'    => $r->priority,
                'status'      => $r->status,
                'room_number' => $r->room?->room_number,
                'created_at'  => $r->created_at,
            ]),
            'total' => $requests->total(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|max:255',
            'description' => 'required',
            'priority'    => 'nullable|in:low,medium,high,urgent',
        ]);

        $occupant = $request->user();
        $lease    = $occupant->leases()->where('status', 'active')->first();

        $record = MaintenanceRequest::create([
            'room_id'     => $lease?->room_id,
            'occupant_id' => $occupant->id,
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => $request->priority ?? 'medium',
            'status'      => 'open',
        ]);

        $record->load('room');
        User::whereIn('role', ['owner', 'staff'])->where('is_active', true)->each(
            fn ($admin) => $admin->notify(new MaintenanceSubmittedNotification($record))
        );

        return response()->json(['message' => 'Laporan berhasil dikirim.', 'id' => $record->id], 201);
    }
}
