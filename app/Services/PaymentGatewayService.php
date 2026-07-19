<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str;

class PaymentGatewayService
{
    private string $activeGateway;

    public function __construct()
    {
        $this->activeGateway = setting('payment_gateway_active', 'manual');
    }

    /**
     * Buat transaksi pembayaran untuk invoice
     */
    public function createTransaction(Invoice $invoice): array
    {
        $orderId = 'KOS-' . $invoice->id . '-' . strtoupper(Str::random(6));

        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'gateway'    => $this->activeGateway,
            'order_id'   => $orderId,
            'amount'     => $invoice->total_with_penalty,
            'status'     => 'pending',
        ]);

        return match ($this->activeGateway) {
            'midtrans' => $this->createMidtransTransaction($transaction, $invoice),
            'tripay'   => $this->createTripayTransaction($transaction, $invoice),
            default    => $this->createManualTransaction($transaction, $invoice),
        };
    }

    // -----------------------------------------------------------------------
    // MIDTRANS
    // -----------------------------------------------------------------------
    private function createMidtransTransaction(PaymentTransaction $trx, Invoice $invoice): array
    {
        $serverKey = setting('midtrans_server_key', '', 'payment');
        $isProduction = setting('midtrans_production', false, 'payment');
        $baseUrl  = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $occupant = $invoice->lease->occupant;

        $payload = [
            'transaction_details' => [
                'order_id'     => $trx->order_id,
                'gross_amount' => (int) $trx->amount,
            ],
            'customer_details' => [
                'first_name' => $occupant->name,
                'email'      => $occupant->email ?? '',
                'phone'      => $occupant->phone,
            ],
            'item_details' => [
                [
                    'id'       => 'INV-' . $invoice->id,
                    'price'    => (int) $invoice->total,
                    'quantity' => 1,
                    'name'     => 'Tagihan ' . $invoice->invoice_number,
                ],
            ],
        ];

        if ($invoice->penalty > 0) {
            $payload['item_details'][] = [
                'id'       => 'PENALTY-' . $invoice->id,
                'price'    => (int) $invoice->penalty,
                'quantity' => 1,
                'name'     => 'Denda Keterlambatan',
            ];
        }

        $ch = curl_init($baseUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($serverKey . ':'),
            ],
        ]);
        $response = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201 && isset($response['redirect_url'])) {
            $trx->update([
                'payment_url'      => $response['redirect_url'],
                'gateway_response' => $response,
                'expired_at'       => now()->addHours(24),
            ]);

            return ['success' => true, 'payment_url' => $response['redirect_url'], 'order_id' => $trx->order_id];
        }

        $trx->update(['status' => 'failed', 'gateway_response' => $response]);
        return ['success' => false, 'message' => $response['error_messages'][0] ?? 'Midtrans error'];
    }

    // -----------------------------------------------------------------------
    // TRIPAY
    // -----------------------------------------------------------------------
    private function createTripayTransaction(PaymentTransaction $trx, Invoice $invoice): array
    {
        $apiKey    = setting('tripay_api_key', '', 'payment');
        $privateKey = setting('tripay_private_key', '', 'payment');
        $merchantCode = setting('tripay_merchant_code', '', 'payment');
        $isProduction = setting('tripay_production', false, 'payment');
        $channel   = setting('tripay_default_channel', 'BRIVA', 'payment');

        $baseUrl = $isProduction
            ? 'https://tripay.co.id/api/transaction/create'
            : 'https://tripay.co.id/api-sandbox/transaction/create';

        $occupant  = $invoice->lease->occupant;
        $signature = hash_hmac('sha256', $merchantCode . $trx->order_id . (int) $trx->amount, $privateKey);

        $payload = [
            'method'         => $channel,
            'merchant_ref'   => $trx->order_id,
            'amount'         => (int) $trx->amount,
            'customer_name'  => $occupant->name,
            'customer_email' => $occupant->email ?? '',
            'customer_phone' => $occupant->phone,
            'order_items'    => [
                [
                    'sku'      => 'INV-' . $invoice->id,
                    'name'     => 'Tagihan ' . $invoice->invoice_number,
                    'price'    => (int) $trx->amount,
                    'quantity' => 1,
                ],
            ],
            'signature'      => $signature,
            'expired_time'   => now()->addHours(24)->timestamp,
        ];

        $ch = curl_init($baseUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (($response['success'] ?? false) && isset($response['data']['checkout_url'])) {
            $trx->update([
                'payment_url'      => $response['data']['checkout_url'],
                'transaction_id'   => $response['data']['reference'],
                'gateway_response' => $response,
                'expired_at'       => now()->addHours(24),
            ]);

            return ['success' => true, 'payment_url' => $response['data']['checkout_url'], 'order_id' => $trx->order_id];
        }

        $trx->update(['status' => 'failed', 'gateway_response' => $response]);
        return ['success' => false, 'message' => $response['message'] ?? 'Tripay error'];
    }

    // -----------------------------------------------------------------------
    // MANUAL (transfer bank / tunai)
    // -----------------------------------------------------------------------
    private function createManualTransaction(PaymentTransaction $trx, Invoice $invoice): array
    {
        $bankInfo = setting('manual_bank_info', '', 'payment');

        $trx->update([
            'payment_type' => 'manual',
            'channel'      => 'bank_transfer',
        ]);

        return [
            'success'   => true,
            'manual'    => true,
            'bank_info' => $bankInfo,
            'order_id'  => $trx->order_id,
            'amount'    => $trx->amount,
        ];
    }

    // -----------------------------------------------------------------------
    // Handle webhook / callback dari gateway
    // -----------------------------------------------------------------------
    public function handleCallback(string $gateway, array $payload): bool
    {
        return match ($gateway) {
            'midtrans' => $this->handleMidtransCallback($payload),
            'tripay'   => $this->handleTripayCallback($payload),
            default    => false,
        };
    }

    private function handleMidtransCallback(array $payload): bool
    {
        $serverKey    = setting('midtrans_server_key', '', 'payment');
        $orderId      = $payload['order_id'] ?? '';
        $statusCode   = $payload['status_code'] ?? '';
        $grossAmount  = $payload['gross_amount'] ?? '';
        $expectedHash = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($payload['signature_key'] !== $expectedHash) return false;

        $trx = PaymentTransaction::where('order_id', $orderId)->first();
        if (!$trx) return false;

        $transactionStatus = $payload['transaction_status'] ?? '';

        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            $this->markPaid($trx, $payload);
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            $trx->update(['status' => 'failed', 'gateway_response' => $payload]);
        }

        return true;
    }

    private function handleTripayCallback(array $payload): bool
    {
        $privateKey  = setting('tripay_private_key', '', 'payment');
        $signature   = hash_hmac('sha256', $payload['merchant_ref'] . $payload['amount'], $privateKey);

        if ($payload['signature'] !== $signature) return false;

        $trx = PaymentTransaction::where('order_id', $payload['merchant_ref'])->first();
        if (!$trx) return false;

        if ($payload['status'] === 'PAID') {
            $this->markPaid($trx, $payload);
        }

        return true;
    }

    private function markPaid(PaymentTransaction $trx, array $gatewayData): void
    {
        $trx->update([
            'status'           => 'success',
            'paid_at'          => now(),
            'gateway_response' => $gatewayData,
        ]);

        $trx->invoice->update([
            'status'          => 'paid',
            'paid_at'         => now(),
            'payment_channel' => $trx->gateway,
            'payment_ref'     => $trx->order_id,
        ]);
    }
}
