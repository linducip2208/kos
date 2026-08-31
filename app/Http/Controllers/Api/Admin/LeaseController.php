<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\Room;
use App\Services\LeaseWorkflowService;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class LeaseController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize($request, 'lease.view');
        $propertyIds = $request->user()->scopedPropertyIds();
        $leases = Lease::with(['occupant:id,name,phone', 'room:id,room_number,property_id', 'room.property:id,name'])
            ->when($propertyIds !== null, fn ($query) => $query->whereHas('room', fn ($room) => $room->whereIn('property_id', $propertyIds ?: [0])))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->property_id, fn ($q) => $q->whereHas('room', fn ($q) => $q->where('property_id', $request->property_id)))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json($leases);
    }

    public function store(Request $request)
    {
        $this->authorize($request, 'lease.manage');
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'occupant_id' => 'required|exists:occupants,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'price' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'billing_cycle' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'billing_date' => 'required|integer|min:1|max:28',
            'notes' => 'nullable|string',
        ]);
        $this->assertRoomScope($request, (int) $data['room_id']);
        app(RoomAvailabilityService::class)->assertAssignable(Room::findOrFail($data['room_id']));

        $data['lease_number'] = $this->generateNumber();
        $data['status'] = 'pending';

        $lease = Lease::create($data);

        return response()->json($lease->load(['occupant', 'room.property']), 201);
    }

    public function show(Lease $lease)
    {
        $this->authorizeLease(request(), $lease, 'lease.view');

        return response()->json($lease->load(['occupant', 'room.property', 'invoices']));
    }

    public function update(Request $request, Lease $lease)
    {
        $this->authorizeLease($request, $lease, 'lease.manage');
        $data = $request->validate([
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'price' => 'sometimes|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'billing_cycle' => 'in:daily,weekly,monthly,quarterly,yearly',
            'billing_date' => 'sometimes|integer|min:1|max:28',
            'notes' => 'nullable|string',
        ]);
        $start = isset($data['start_date']) ? $data['start_date'] : $lease->start_date?->toDateString();
        $end = isset($data['end_date']) ? $data['end_date'] : $lease->end_date?->toDateString();
        if ($start && $end && $end < $start) {
            throw ValidationException::withMessages(['end_date' => 'Tanggal selesai harus setelah tanggal mulai.']);
        }

        $lease->update($data);

        return response()->json($lease);
    }

    public function destroy(Lease $lease)
    {
        $this->authorizeLease(request(), $lease, 'lease.manage');
        if ($lease->status === 'active') {
            return response()->json(['message' => 'Kontrak aktif tidak dapat dihapus. Terminasi terlebih dahulu.'], 422);
        }
        if ($lease->invoices()->exists() || $lease->checkinRecords()->exists() || $lease->deposits()->exists()) {
            return response()->json(['message' => 'Kontrak memiliki histori transaksi dan tidak dapat dihapus.'], 422);
        }

        $lease->delete();

        return response()->json(null, 204);
    }

    public function terminate(Request $request, Lease $lease)
    {
        $this->authorizeLease($request, $lease, 'lease.manage');
        $request->validate(['reason' => 'nullable|string']);

        app(LeaseWorkflowService::class)->terminate($lease, $request->reason ?? 'Terminasi melalui API');

        return response()->json(['message' => 'Kontrak diterminasi.', 'lease' => $lease]);
    }

    public function expiring(Request $request)
    {
        $this->authorize($request, 'lease.view');
        $days = min(365, max(1, (int) ($request->days ?? 30)));
        $propertyIds = $request->user()->scopedPropertyIds();

        return response()->json(
            Lease::where('status', 'active')
                ->when($propertyIds !== null, fn ($query) => $query->whereHas('room', fn ($room) => $room->whereIn('property_id', $propertyIds ?: [0])))
                ->whereBetween('end_date', [now(), now()->addDays($days)])
                ->with(['occupant:id,name,phone', 'room:id,room_number,property_id', 'room.property:id,name'])
                ->get()
        );
    }

    public function invoices(Lease $lease)
    {
        $this->authorizeLease(request(), $lease, 'invoice.view');

        return response()->json($lease->invoices()->latest()->get());
    }

    private function authorize(Request $request, string $permission): void
    {
        abort_unless(Gate::forUser($request->user())->allows($permission), 403);
    }

    private function authorizeLease(Request $request, Lease $lease, string $permission): void
    {
        $this->authorize($request, $permission);
        $propertyIds = $request->user()->scopedPropertyIds();
        abort_unless($propertyIds === null || in_array($lease->loadMissing('room')->room?->property_id, $propertyIds, true), 403);
    }

    private function assertRoomScope(Request $request, int $roomId): void
    {
        $propertyIds = $request->user()->scopedPropertyIds();
        abort_unless($propertyIds === null || Room::whereKey($roomId)->whereIn('property_id', $propertyIds ?: [0])->exists(), 403);
    }

    private function generateNumber(): string
    {
        $prefix = setting('lease_prefix', 'KTR');
        $year = date('Y');
        $count = Lease::whereYear('created_at', $year)->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $year, $count);
    }
}
