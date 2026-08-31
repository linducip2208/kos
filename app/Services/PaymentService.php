<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function recordPayment(Invoice $invoice, array $data): InvoicePayment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);
            if ($invoice->status === 'cancelled') {
                throw ValidationException::withMessages(['invoice' => 'Invoice sudah dibatalkan.']);
            }

            $amount = round((float) ($data['amount'] ?? 0), 2);
            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'Nominal harus lebih dari nol.']);
            }

            $reference = $data['reference'] ?? null;
            if ($reference && InvoicePayment::where('reference', $reference)->whereIn('status', ['pending_verification', 'verified'])->exists()) {
                throw ValidationException::withMessages(['reference' => 'Referensi pembayaran sudah pernah dicatat.']);
            }

            $reserved = (float) $invoice->payments()->where('type', 'payment')->whereIn('status', ['pending_verification', 'verified'])->sum('amount');
            if ($amount > max(0, (float) $invoice->payable_amount - $reserved) + 0.009) {
                throw ValidationException::withMessages(['amount' => 'Nominal melebihi sisa tagihan invoice.']);
            }

            $status = $data['status'] ?? 'verified';
            $payment = InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'type' => 'payment',
                'amount' => $amount,
                'method' => $data['method'] ?? 'cash',
                'reference' => $reference,
                'proof_path' => $data['proof_path'] ?? null,
                'paid_at' => Carbon::parse($data['paid_at'] ?? now()),
                'received_by' => $data['received_by'] ?? auth()->id(),
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'payment_transaction_id' => $data['payment_transaction_id'] ?? null,
                'verified_by' => $status === 'verified' ? auth()->id() : null,
                'verified_at' => $status === 'verified' ? now() : null,
            ]);

            if ($status === 'verified') {
                $invoice->syncPaymentStatus();
            }

            return $payment;
        });
    }

    public function hasDuplicateReference(string $reference): bool
    {
        return blank($reference) ? false : InvoicePayment::where('type', 'payment')->where('reference', $reference)->whereIn('status', ['pending_verification', 'verified'])->exists();
    }

    public function verify(InvoicePayment $payment, User $verifier): InvoicePayment
    {
        return DB::transaction(function () use ($payment, $verifier) {
            $payment = InvoicePayment::query()->lockForUpdate()->findOrFail($payment->id);
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($payment->invoice_id);
            if ($payment->status !== 'pending_verification') {
                throw ValidationException::withMessages(['status' => 'Pembayaran ini tidak dalam status menunggu verifikasi.']);
            }
            if ((float) $payment->amount > (float) $invoice->balance_due + 0.009) {
                throw ValidationException::withMessages(['amount' => 'Pembayaran melebihi sisa tagihan.']);
            }
            $payment->forceFill(['status' => 'verified', 'verified_by' => $verifier->id, 'verified_at' => now()])->save();
            $invoice->syncPaymentStatus();

            return $payment;
        });
    }

    public function reject(InvoicePayment $payment, User $verifier, string $reason): InvoicePayment
    {
        if ($payment->status !== 'pending_verification') {
            throw ValidationException::withMessages(['status' => 'Hanya pembayaran pending yang bisa ditolak.']);
        }
        $payment->forceFill(['status' => 'rejected', 'verified_by' => $verifier->id, 'verified_at' => now(), 'rejection_reason' => $reason])->save();

        return $payment;
    }

    public function refund(Invoice $invoice, float $amount, string $reason, ?User $by = null): InvoicePayment
    {
        return DB::transaction(function () use ($invoice, $amount, $reason, $by) {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);
            $paid = (float) $invoice->paid_amount;
            if ($amount <= 0 || $amount > $paid + 0.009) {
                throw ValidationException::withMessages(['amount' => 'Refund melebihi pembayaran terverifikasi.']);
            }
            $refund = InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'type' => 'refund',
                'amount' => round($amount, 2),
                'method' => 'transfer',
                'reason' => $reason,
                'paid_at' => now(),
                'received_by' => $by?->id ?? auth()->id(),
                'status' => 'verified',
                'verified_by' => $by?->id ?? auth()->id(),
                'verified_at' => now(),
            ]);
            $invoice->syncPaymentStatus();

            return $refund;
        });
    }

    public function markPaidWithRecord(Invoice $invoice, string $method = 'cash', ?string $reference = null): InvoicePayment
    {
        if ($invoice->balance_due <= 0.009) {
            throw ValidationException::withMessages(['invoice' => 'Invoice sudah lunas.']);
        }

        return $this->recordPayment($invoice, ['amount' => $invoice->balance_due, 'method' => $method, 'paid_at' => now(), 'reference' => $reference, 'status' => 'verified']);
    }
}
