<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize($request, 'property.view');
        $propertyIds = $request->user()->scopedPropertyIds();

        return response()->json(
            Property::withCount(['rooms', 'rooms as available_count' => fn ($q) => $q->where('status', 'available')])
                ->when($propertyIds !== null, fn ($query) => $query->whereIn('id', $propertyIds ?: [0]))
                ->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $this->authorize($request, 'property.manage');
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'facilities' => 'nullable|array',
            'rules' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $property = Property::create($data);

        return response()->json($property, 201);
    }

    public function show(Property $property)
    {
        $this->authorizeProperty(request(), $property, 'property.view');
        $property->load(['roomTypes', 'rooms.roomType']);

        return response()->json($property);
    }

    public function update(Request $request, Property $property)
    {
        $this->authorizeProperty($request, $property, 'property.manage');
        $data = $request->validate([
            'name' => 'sometimes|string|max:150',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'province' => 'sometimes|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'facilities' => 'nullable|array',
            'rules' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $property->update($data);

        return response()->json($property);
    }

    public function destroy(Property $property)
    {
        $this->authorizeProperty(request(), $property, 'property.manage');
        if ($property->rooms()->exists() || $property->leases()->exists()) {
            return response()->json(['message' => 'Properti memiliki histori operasional dan tidak dapat dihapus.'], 422);
        }
        $property->delete();

        return response()->json(['message' => 'Properti dihapus.']);
    }

    private function authorize(Request $request, string $permission): void
    {
        abort_unless(Gate::forUser($request->user())->allows($permission), 403);
    }

    private function authorizeProperty(Request $request, Property $property, string $permission): void
    {
        $this->authorize($request, $permission);
        $propertyIds = $request->user()->scopedPropertyIds();
        abort_unless($propertyIds === null || in_array($property->id, $propertyIds, true), 403);
    }
}
