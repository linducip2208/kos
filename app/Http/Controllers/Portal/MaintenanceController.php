<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    public function index()
    {
        $occupant = Auth::guard('portal')->user();
        $requests = $occupant->maintenanceRequests()->with('room')->latest()->paginate(10);
        return view('portal.maintenance.index', compact('requests'));
    }

    public function create()
    {
        $occupant = Auth::guard('portal')->user();
        $lease    = $occupant->leases()->where('status', 'active')->with('room')->first();
        return view('portal.maintenance.create', compact('occupant', 'lease'));
    }

    public function store(Request $request)
    {
        $occupant = Auth::guard('portal')->user();
        $lease    = $occupant->leases()->where('status', 'active')->first();

        $request->validate([
            'title'       => 'required|max:255',
            'description' => 'required',
            'priority'    => 'required|in:low,medium,high,urgent',
        ]);

        MaintenanceRequest::create([
            'room_id'     => $lease?->room_id,
            'occupant_id' => $occupant->id,
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => $request->priority,
            'status'      => 'open',
        ]);

        return redirect()->route('portal.maintenance.index')
            ->with('success', 'Laporan kerusakan berhasil dikirim.');
    }
}
