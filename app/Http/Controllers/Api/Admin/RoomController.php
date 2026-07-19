<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Property;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::with(['property:id,name', 'roomType:id,name'])
            ->when($request->property_id, fn ($q) => $q->where('property_id', $request->property_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->get();

        return response()->json($rooms);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id'  => 'required|exists:properties,id',
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_number'  => 'required|string|max:20',
            'floor'        => 'nullable|integer',
            'status'       => 'in:available,occupied,maintenance,reserved',
            'notes'        => 'nullable|string',
        ]);

        $room = Room::create($data);
        return response()->json($room->load(['property:id,name', 'roomType:id,name']), 201);
    }

    public function show(Room $room)
    {
        return response()->json($room->load(['property', 'roomType', 'activeLease.occupant']));
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_number'  => 'sometimes|string|max:20',
            'floor'        => 'nullable|integer',
            'notes'        => 'nullable|string',
        ]);

        $room->update($data);
        return response()->json($room);
    }

    public function updateStatus(Request $request, Room $room)
    {
        $request->validate([
            'status' => 'required|in:available,occupied,maintenance,reserved',
        ]);

        $room->update(['status' => $request->status]);
        return response()->json(['id' => $room->id, 'status' => $room->status]);
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return response()->json(['message' => 'Kamar dihapus.']);
    }

    public function available()
    {
        return response()->json(
            Room::where('status', 'available')
                ->with(['property:id,name', 'roomType:id,name,price_monthly'])
                ->get()
        );
    }
}
