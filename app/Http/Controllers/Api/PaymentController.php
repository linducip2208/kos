<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentGatewayService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

/**
 * Pembayaran tagihan untuk aplikasi mobile:
 * - pay: buat transaksi gateway (Midtrans/Tripay/manual) → kembalikan instruksi bayar.
 * - proof: upload bukti transfer manual → menunggu verifikasi admin.
 */
class PaymentController extends Controller
{
    public function pay(Request $request, int $id)
    {
        $invoice = $request->user()->invoices()->findOrFail($id);

        if (in_array($invoice->status, ['paid', 'cancelled', 'draft'], true)) {
            return response()->json(['message' => 'Invoice sudah lunas atau tidak dapat dibayar.'], 422);
        }

        $result = app(PaymentGatewayService::class)->createTransaction($invoice);

        if (! ($result['success'] ?? false)) {
            return response()->json(['message' => $result['message'] ?? 'Gagal membuat transaksi.'], 422);
        }

        return response()->json([
            'order_id'    => $result['order_id'] ?? null,
            'amount'      => $invoice->total_with_penalty,
            'payment_url' => $result['payment_url'] ?? null,
            'manual'      => $result['manual'] ?? false,
            'bank_info'   => $result['bank_info'] ?? null,
        ]);
    }

    public function proof(Request $request, int $id)
    {
        $invoice = $request->user()->invoices()->findOrFail($id);

        if (in_array($invoice->status, ['paid', 'cancelled', 'draft'], true)) {
            return response()->json(['message' => 'Invoice sudah lunas atau tidak dapat dibayar.'], 422);
        }

        $request->validate([
            'amount'    => 'required|numeric|min:1000',
            'method'    => 'nullable|in:transfer,va,qris,cash,other',
            'reference' => 'nullable|max:120',
            'proof'     => 'required|image|mimes:png,jpg,jpeg|max:2048',
            'notes'     => 'nullable|max:500',
        ]);

        $path = $request->file('proof')->store('payments/proofs', 'public');

        try {
            $payment = app(PaymentService::class)->recordPayment($invoice, [
                'amount'      => $request->amount,
                'method'      => $request->method ?? 'transfer',
                'reference'   => $request->reference,
                'proof_path'  => $path,
                'status'      => 'pending_verification',
                'received_by' => null,
                'notes'       => $request->notes,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        }

        return response()->json([
            'message'    => 'Bukti pembayaran terkirim. Menunggu verifikasi admin.',
            'payment_id' => $payment->id,
        ], 201);
    }
}
