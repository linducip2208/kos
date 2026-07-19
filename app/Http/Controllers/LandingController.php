<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use App\Models\Faq;
use App\Models\Property;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function home()
    {
        $properties = Property::where('is_active', true)
            ->withCount(['rooms as available_rooms' => fn($q) => $q->where('status', 'available')])
            ->withCount(['rooms as total_rooms'])
            ->with('roomTypes')
            ->get();

        if ($properties->count() === 1) {
            return redirect()->route('landing.property', $properties->first());
        }

        $testimonials = Testimonial::active()
            ->whereNull('property_id')
            ->orWhere('is_active', true)
            ->limit(6)
            ->get();

        return view('landing.home', compact('properties', 'testimonials'));
    }

    public function property(Property $property)
    {
        abort_if(! $property->is_active, 404);

        $roomTypes = $property->roomTypes()
            ->with(['rooms' => fn($q) => $q->where('status', 'available')])
            ->get()
            ->map(function ($type) {
                $type->available_count = $type->rooms->count();
                return $type;
            });

        $availableTotal = $roomTypes->sum('available_count');

        $faqs = Faq::active()
            ->where(fn($q) => $q->whereNull('property_id')->orWhere('property_id', $property->id))
            ->get();

        $testimonials = Testimonial::active()
            ->where(fn($q) => $q->whereNull('property_id')->orWhere('property_id', $property->id))
            ->limit(4)
            ->get();

        return view('landing.property', compact('property', 'roomTypes', 'availableTotal', 'faqs', 'testimonials'));
    }

    public function contact(Request $request)
    {
        $request->validate([
            'name'        => 'required|max:150',
            'phone'       => 'required|max:20',
            'email'       => 'nullable|email|max:150',
            'subject'     => 'nullable|max:200',
            'message'     => 'required|max:2000',
            'property_id' => 'nullable|exists:properties,id',
        ]);

        ContactSubmission::create([
            'property_id' => $request->property_id,
            'name'        => $request->name,
            'phone'       => $request->phone,
            'email'       => $request->email,
            'subject'     => $request->subject,
            'message'     => $request->message,
            'ip_address'  => $request->ip(),
            'status'      => 'new',
        ]);

        return redirect()->back()->with('contact_success', true);
    }
}
