<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Models\Property;
use App\Services\RoomAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Booking publik untuk aplikasi mobile.
 * Mirror dari App\Http\Controllers\BookingController@store.
 */
class BookingController extends Controller
{
    public function store(Request $request, string $key)
    {
        $property = Property::query()
            ->where('slug', $key)
            ->orWhere(fn ($q) => $q->whereKey($key)->where('is_active', true))
            ->active()
            ->firstOrFail();

        $request->validate([
            'name'            => 'required|max:255',
            'phone'           => 'required|max:20',
            'email'           => 'nullable|email',
            'whatsapp'        => 'nullable|max:20',
            'desired_move_in' => 'required|date|after:today',
            'billing_cycle'   => 'required|in:monthly,quarterly,yearly',
            'room_type_id'    => 'nullable|exists:room_types,id',
            'room_id'         => 'nullable|exists:rooms,id',
            'message'         => 'nullable|max:1000',
        ]);

        if ($request->filled('room_type_id') && ! $property->roomTypes()->whereKey($request->integer('room_type_id'))->exists()) {
            throw ValidationException::withMessages(['room_type_id' => 'Tipe kamar tidak berasal dari properti yang dipilih.']);
        }

        if ($request->filled('room_id')) {
            $room = $property->rooms()->findOrFail($request->integer('room_id'));
            app(RoomAvailabilityService::class)->assertBookingAvailable($room, Carbon::parse($request->input('desired_move_in')));
        }

        $booking = BookingRequest::create([
            'property_id'     => $property->id,
            'name'            => $request->name,
            'phone'           => $request->phone,
            'email'           => $request->email,
            'whatsapp'        => $request->whatsapp,
            'desired_move_in' => $request->desired_move_in,
            'billing_cycle'   => $request->billing_cycle,
            'room_type_id'    => $request->room_type_id,
            'room_id'         => $request->room_id,
            'message'         => $request->message,
            'status'          => 'pending',
        ]);

        return response()->json([
            'message' => 'Booking diterima. Admin akan menghubungi Anda via WhatsApp.',
            'id'      => $booking->id,
        ], 201);
    }
}
