<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use Illuminate\Http\Request;

class LeaseController extends Controller
{
    public function index(Request $request)
    {
        $leases = Lease::with(['occupant:id,name,phone', 'room:id,room_number,property_id', 'room.property:id,name'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->property_id, fn ($q) => $q->whereHas('room', fn ($q) => $q->where('property_id', $request->property_id)))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json($leases);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id'       => 'required|exists:rooms,id',
            'occupant_id'   => 'required|exists:occupants,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'price'         => 'required|numeric|min:0',
            'deposit'       => 'nullable|numeric|min:0',
            'billing_cycle' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'billing_date'  => 'required|integer|min:1|max:31',
            'notes'         => 'nullable|string',
        ]);

        $data['lease_number'] = $this->generateNumber();
        $data['status']       = 'pending';

        $lease = Lease::create($data);
        return response()->json($lease->load(['occupant', 'room.property']), 201);
    }

    public function show(Lease $lease)
    {
        return response()->json($lease->load(['occupant', 'room.property', 'invoices']));
    }

    public function update(Request $request, Lease $lease)
    {
        $data = $request->validate([
            'start_date'    => 'sometimes|date',
            'end_date'      => 'sometimes|date',
            'price'         => 'sometimes|numeric|min:0',
            'deposit'       => 'nullable|numeric|min:0',
            'billing_cycle' => 'in:daily,weekly,monthly,quarterly,yearly',
            'billing_date'  => 'integer|min:1|max:31',
            'status'        => 'in:active,expired,terminated,pending',
            'notes'         => 'nullable|string',
        ]);

        $lease->update($data);
        return response()->json($lease);
    }

    public function destroy(Lease $lease)
    {
        if ($lease->status === 'active') {
            return response()->json(['message' => 'Kontrak aktif tidak dapat dihapus. Terminasi terlebih dahulu.'], 422);
        }

        $lease->delete();
        return response()->json(null, 204);
    }

    public function terminate(Request $request, Lease $lease)
    {
        $request->validate(['reason' => 'nullable|string']);

        $lease->update([
            'status'               => 'terminated',
            'terminated_at'        => now()->toDateString(),
            'termination_reason'   => $request->reason,
        ]);

        return response()->json(['message' => 'Kontrak diterminasi.', 'lease' => $lease]);
    }

    public function expiring(Request $request)
    {
        $days = $request->days ?? 30;

        return response()->json(
            Lease::where('status', 'active')
                ->whereBetween('end_date', [now(), now()->addDays($days)])
                ->with(['occupant:id,name,phone', 'room:id,room_number,property_id', 'room.property:id,name'])
                ->get()
        );
    }

    public function invoices(Lease $lease)
    {
        return response()->json($lease->invoices()->latest()->get());
    }

    private function generateNumber(): string
    {
        $prefix = setting('lease_prefix', 'KTR');
        $year   = date('Y');
        $count  = Lease::whereYear('created_at', $year)->count() + 1;
        return sprintf('%s-%s-%04d', $prefix, $year, $count);
    }
}
