<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\RoomStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize($request, 'room.view');
        $propertyIds = $request->user()->scopedPropertyIds();
        $rooms = Room::with(['property:id,name', 'roomType:id,name'])
            ->when($propertyIds !== null, fn ($query) => $query->whereIn('property_id', $propertyIds ?: [0]))
            ->when($request->property_id, fn ($q) => $q->where('property_id', $request->property_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->get();

        return response()->json($rooms);
    }

    public function store(Request $request)
    {
        $this->authorize($request, 'room.manage');
        $data = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_number' => 'required|string|max:20',
            'floor' => 'nullable|integer|min:0|max:99',
            'status' => 'sometimes|in:available',
            'notes' => 'nullable|string',
        ]);
        $this->assertPropertyScope($request, (int) $data['property_id']);
        $data['status'] = 'available';

        $room = Room::create($data);

        return response()->json($room->load(['property:id,name', 'roomType:id,name']), 201);
    }

    public function show(Room $room)
    {
        $this->authorizeRoom(request(), $room, 'room.view');

        return response()->json($room->load(['property', 'roomType', 'activeLease.occupant']));
    }

    public function update(Request $request, Room $room)
    {
        $this->authorizeRoom($request, $room, 'room.manage');
        $data = $request->validate([
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_number' => 'sometimes|string|max:20',
            'floor' => 'nullable|integer|min:0|max:99',
            'notes' => 'nullable|string',
        ]);

        $room->update($data);

        return response()->json($room);
    }

    public function updateStatus(Request $request, Room $room)
    {
        $this->authorizeRoom($request, $room, 'room.manage');
        $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(Room::STATUSES)),
        ]);

        app(RoomStatusService::class)->transition($room, $request->status, $request->input('reason'));

        return response()->json(['id' => $room->id, 'status' => $room->status]);
    }

    public function destroy(Room $room)
    {
        $this->authorizeRoom(request(), $room, 'room.manage');
        if ($room->leases()->exists() || $room->bookings()->exists() || $room->maintenanceRequests()->exists()) {
            return response()->json(['message' => 'Kamar memiliki histori dan tidak dapat dihapus.'], 422);
        }
        $room->delete();

        return response()->json(['message' => 'Kamar dihapus.']);
    }

    public function available()
    {
        $user = request()->user();
        abort_unless(Gate::forUser($user)->allows('room.view'), 403);
        $propertyIds = $user->scopedPropertyIds();

        return response()->json(
            Room::where('status', 'available')
                ->when($propertyIds !== null, fn ($query) => $query->whereIn('property_id', $propertyIds ?: [0]))
                ->with(['property:id,name', 'roomType:id,name,price_monthly'])
                ->get()
        );
    }

    private function authorize(Request $request, string $permission): void
    {
        abort_unless(Gate::forUser($request->user())->allows($permission), 403);
    }

    private function authorizeRoom(Request $request, Room $room, string $permission): void
    {
        $this->authorize($request, $permission);
        $this->assertPropertyScope($request, (int) $room->property_id);
    }

    private function assertPropertyScope(Request $request, int $propertyId): void
    {
        $ids = $request->user()->scopedPropertyIds();
        abort_unless($ids === null || in_array($propertyId, $ids, true), 403);
    }
}
