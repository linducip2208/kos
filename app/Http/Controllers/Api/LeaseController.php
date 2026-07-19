<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LeaseController extends Controller
{
    public function active(Request $request)
    {
        $lease = $request->user()->leases()
            ->where('status', 'active')
            ->with(['room.property', 'room.roomType'])
            ->latest()->first();

        if (!$lease) {
            return response()->json(['message' => 'Tidak ada kontrak aktif.'], 404);
        }

        return response()->json([
            'id'           => $lease->id,
            'lease_number' => $lease->lease_number,
            'start_date'   => $lease->start_date,
            'end_date'     => $lease->end_date,
            'status'       => $lease->status,
            'billing_cycle'=> $lease->billing_cycle,
            'rent_amount'  => $lease->rent_amount,
            'room' => [
                'id'          => $lease->room?->id,
                'room_number' => $lease->room?->room_number,
                'property'    => $lease->room?->property?->name,
                'address'     => $lease->room?->property?->address,
            ],
        ]);
    }
}
