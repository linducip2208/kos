<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\EContract;
use App\Services\EContractService;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function show(string $token)
    {
        $contract = EContract::where('sign_token', $token)
            ->with(['lease.occupant', 'lease.room.property'])
            ->first();

        abort_unless($contract && $contract->isTokenValid($token), 404);

        return view('portal.contract.sign', compact('contract'));
    }

    public function sign(Request $request, string $token)
    {
        $contract = EContract::where('sign_token', $token)->first();

        abort_unless($contract && $contract->isTokenValid($token), 404);

        $request->validate([
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
            'agree' => 'required|accepted',
        ]);

        $service = app(EContractService::class);
        $path = $service->saveSignatureUpload($request->file('signature'), 'occupant_'.$contract->id);
        $contract->update([
            'occupant_signature' => $path,
            'occupant_signed_at' => now(),
            'status' => $contract->owner_signature ? 'fully_signed' : 'sent',
            'sign_token' => null,
            'sign_token_expires_at' => null,
        ]);

        return view('portal.contract.signed', compact('contract'));
    }
}
