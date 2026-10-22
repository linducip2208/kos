<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EContract;
use App\Services\EContractService;
use Illuminate\Http\Request;

/**
 * E-Contract via token (tanpa login; token = otorisasi).
 * Mirror dari App\Http\Controllers\Portal\ContractController.
 */
class ContractController extends Controller
{
    public function show(string $token)
    {
        $contract = EContract::where('sign_token', $token)
            ->with(['lease.occupant', 'lease.room.property'])
            ->first();

        abort_unless($contract && $contract->isTokenValid($token), 404);

        $lease = $contract->lease;

        return response()->json([
            'id'                => $contract->id,
            'status'            => $contract->status,
            'status_label'      => $contract->status_label ?? $contract->status,
            'occupant_signed'   => ! is_null($contract->occupant_signed_at),
            'pdf_url'           => $contract->pdf_path ? url('storage/'.$contract->pdf_path) : null,
            'expires_at'        => $contract->sign_token_expires_at,
            'lease'             => $lease ? [
                'room_number' => $lease->room?->room_number,
                'property'    => $lease->room?->property?->name,
                'occupant'    => $lease->occupant?->name,
                'start_date'  => $lease->start_date,
                'end_date'    => $lease->end_date,
            ] : null,
        ]);
    }

    public function sign(Request $request, string $token)
    {
        $contract = EContract::where('sign_token', $token)->first();

        abort_unless($contract && $contract->isTokenValid($token), 404);

        $request->validate([
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
            'agree'     => 'required|accepted',
        ]);

        $service = app(EContractService::class);
        $path = $service->saveSignatureUpload($request->file('signature'), 'occupant_'.$contract->id);
        $contract->update([
            'occupant_signature'  => $path,
            'occupant_signed_at'  => now(),
            'status'              => $contract->owner_signature ? 'fully_signed' : 'sent',
            'sign_token'          => null,
            'sign_token_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Kontrak berhasil ditandatangani.',
            'status'  => $contract->status,
        ]);
    }
}
