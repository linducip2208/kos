<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pencatatan pembayaran per invoice dengan alokasi parsial,
 * verifikasi bukti, refund, dan sinkronisasi status otomatis.
 */
class PaymentService
{
    /**
     * Catat pembayaran baru.
     *
     * @param array{amount:float,method:string,paid_at?:\DateTimeInterface|string,reference?:string,
     *              proof_path?:string,status?:string,received_by?:int,notes?:string,
     *              payment_transaction_id?:int} $data
     */
    public function recordPayment(Invoice $invoice, array $data): InvoicePayment
    {
        if ($invoice->status === 'cancelled') {
            throw ValidationException::withMessages(['invoice' => 'Invoice sudah dibatalkan.']);
        }

        $amount = round((float) $data['amount'], 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Nominal harus lebih dari nol.']);
        }

        $status = $data['status'] ?? 'verified';

        // Pembayaran gateway yang sukses langsung verified.
        // Transfer manual bisa pending_verification bila diminta.
        $payment = InvoicePayment::create([
            'invoice_id'             => $invoice->id,
            'type'                   => 'payment',
            'amount'                 => $amount,
            'method'                 => $data['method'] ?? 'cash',
            'reference'              => $data['reference'] ?? null,
            'proof_path'             => $data['proof_path'] ?? null,
            'paid_at'                => \Illuminate\Support\Carbon::parse($data['paid_at'] ?? now()),
            'received_by'            => $data['received_by'] ?? auth()->id(),
            'status'                 => $status,
            'notes'                  => $data['notes'] ?? null,
            'payment_transaction_id' => $data['payment_transaction_id'] ?? null,
            'verified_by'            => $status === 'verified' ? auth()->id() : null,
            'verified_at'            => $status === 'verified' ? now() : null,
        ]);

        $invoice->syncPaymentStatus();

        return $payment;
    }

    /**
     * Idempotency guard untuk webhook gateway:
     * cek apakah reference/order-id sudah tercatat sbg payment verified.
     */
    public function hasDuplicateReference(string $reference): bool
    {
        if (blank($reference)) {
            return false;
        }

        return InvoicePayment::where('type', 'payment')
            ->where('reference', $reference)
            ->where('status', 'verified')
            ->exists();
    }

    public function verify(InvoicePayment $payment, User $verifier): InvoicePayment
    {
        if ($payment->status !== 'pending_verification') {
            throw ValidationException::withMessages(['status' => 'Pembayaran ini tidak dalam status menunggu verifikasi.']);
        }

        $payment->forceFill([
            'status'      => 'verified',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ])->save();

        $payment->invoice->syncPaymentStatus();

        return $payment;
    }

    public function reject(InvoicePayment $payment, User $verifier, string $reason): InvoicePayment
    {
        if ($payment->status !== 'pending_verification') {
            throw ValidationException::withMessages(['status' => 'Hanya pembayaran pending yang bisa ditolak.']);
        }

        $payment->forceFill([
            'status'           => 'rejected',
            'verified_by'      => $verifier->id,
            'verified_at'      => now(),
            'rejection_reason' => $reason,
        ])->save();

        $payment->invoice->syncPaymentStatus();

        return $payment;
    }

    /**
     * Refund pembayaran lunas / sebagian.
     */
    public function refund(Invoice $invoice, float $amount, string $reason, ?User $by = null): InvoicePayment
    {
        $paid = $invoice->paid_amount;

        if ($amount <= 0 || $amount > $paid) {
            throw ValidationException::withMessages([
                'amount' => "Refund maksimal Rp ".number_format($paid, 0, ',', '.').'.',
            ]);
        }

        DB::beginTransaction();
        try {
            $refund = InvoicePayment::create([
                'invoice_id'  => $invoice->id,
                'type'        => 'refund',
                'amount'      => round($amount, 2),
                'method'      => 'transfer',
                'reason'      => $reason,
                'paid_at'     => now(),
                'received_by' => $by?->id ?? auth()->id(),
                'status'      => 'verified',
                'verified_by' => $by?->id ?? auth()->id(),
                'verified_at' => now(),
            ]);

            $invoice->syncPaymentStatus();

            DB::commit();

            return $refund;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark legacy paid tanpa payment record — buat record sintetis agar ledger konsisten.
     */
    public function markPaidWithRecord(Invoice $invoice, string $method = 'cash', ?string $reference = null): InvoicePayment
    {
        return $this->recordPayment($invoice, [
            'amount'   => $invoice->balance_due > 0 ? $invoice->balance_due : $invoice->payable_amount,
            'method'   => $method,
            'paid_at'  => now(),
            'reference'=> $reference,
            'status'   => 'verified',
        ]);
    }
}
