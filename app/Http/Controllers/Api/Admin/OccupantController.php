<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Occupant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OccupantController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize($request, 'tenant.view');
        $propertyIds = $request->user()->scopedPropertyIds();
        $occupants = Occupant::query()
            ->when($propertyIds !== null, fn ($query) => $query->whereHas('leases.room', fn ($room) => $room->whereIn('property_id', $propertyIds ?: [0])))
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            }))
            ->withCount(['leases', 'leases as active_lease_count' => fn ($q) => $q->where('status', 'active')])
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json($occupants);
    }

    public function store(Request $request)
    {
        $this->authorize($request, 'tenant.manage');
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'whatsapp' => 'nullable|string|max:20',
            'id_number' => 'nullable|string|max:30',
            'id_type' => 'in:ktp,sim,passport',
            'address' => 'nullable|string',
            'occupation' => 'nullable|string|max:100',
            'workplace' => 'nullable|string|max:150',
            'emergency_contact' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $occupant = Occupant::create($data);

        return response()->json($occupant, 201);
    }

    public function show(Occupant $occupant)
    {
        $this->authorizeOccupant(request(), $occupant, 'tenant.view');

        return response()->json($occupant->load(['leases.room.property']));
    }

    public function update(Request $request, Occupant $occupant)
    {
        $this->authorizeOccupant($request, $occupant, 'tenant.manage');
        $data = $request->validate([
            'name' => 'sometimes|string|max:150',
            'phone' => 'sometimes|string|max:20',
            'email' => 'nullable|email',
            'whatsapp' => 'nullable|string|max:20',
            'id_number' => 'nullable|string|max:30',
            'id_type' => 'in:ktp,sim,passport',
            'address' => 'nullable|string',
            'occupation' => 'nullable|string|max:100',
            'workplace' => 'nullable|string|max:150',
            'emergency_contact' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $occupant->update($data);

        return response()->json($occupant);
    }

    public function destroy(Occupant $occupant)
    {
        $this->authorizeOccupant(request(), $occupant, 'tenant.manage');
        if ($occupant->leases()->exists()) {
            return response()->json(['message' => 'Penyewa memiliki histori kontrak dan tidak dapat dihapus.'], 422);
        }
        $occupant->delete();

        return response()->json(['message' => 'Penyewa dihapus.']);
    }

    public function leases(Occupant $occupant)
    {
        $this->authorizeOccupant(request(), $occupant, 'lease.view');

        return response()->json($occupant->leases()->with('room.property')->latest()->get());
    }

    private function authorize(Request $request, string $permission): void
    {
        abort_unless(Gate::forUser($request->user())->allows($permission), 403);
    }

    private function authorizeOccupant(Request $request, Occupant $occupant, string $permission): void
    {
        $this->authorize($request, $permission);
        $propertyIds = $request->user()->scopedPropertyIds();
        abort_unless($propertyIds === null || $occupant->leases()->whereHas('room', fn ($room) => $room->whereIn('property_id', $propertyIds ?: [0]))->exists(), 403);
    }
}
