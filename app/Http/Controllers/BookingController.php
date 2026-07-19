<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function show(Property $property)
    {
        $rooms      = $property->rooms()->where('status', 'available')->with('roomType')->get();
        $roomTypes  = RoomType::where('property_id', $property->id)->get();
        return view('booking.show', compact('property', 'rooms', 'roomTypes'));
    }

    public function store(Request $request, Property $property)
    {
        $request->validate([
            'name'             => 'required|max:255',
            'phone'            => 'required|max:20',
            'email'            => 'nullable|email',
            'whatsapp'         => 'nullable|max:20',
            'desired_move_in'  => 'required|date|after:today',
            'billing_cycle'    => 'required|in:monthly,quarterly,yearly',
            'room_type_id'     => 'nullable|exists:room_types,id',
            'room_id'          => 'nullable|exists:rooms,id',
            'message'          => 'nullable|max:1000',
        ]);

        BookingRequest::create([
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

        return redirect()->back()->with('booking_success', true);
    }
}
