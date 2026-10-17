<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(private PaymentGatewayService $gateway) {}

    public function midtrans(Request $request): Response
    {
        $payload = $request->all();

        // Fail-closed: tanpa server key, signature bisa diprediksi penyerang.
        $serverKey  = setting('midtrans_server_key', '', 'payment');
        if ($serverKey === '') {
            Log::warning('Webhook Midtrans ditolak: server key belum dikonfigurasi.');
            abort(500, 'Payment gateway belum dikonfigurasi.');
        }

        $orderId    = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmt   = $payload['gross_amount'] ?? '';
        $expected   = hash('sha512', $orderId . $statusCode . $grossAmt . $serverKey);

        if (($payload['signature_key'] ?? '') !== $expected) {
            return response('Invalid signature', 403);
        }

        // Idempotency: callback ganda untuk transaksi sukses tidak diproses ulang.
        if ($orderId !== '' && $this->alreadyPaid($orderId)) {
            return response('OK', 200);
        }

        $this->gateway->handleCallback('midtrans', $payload);
        return response('OK', 200);
    }

    public function tripay(Request $request): Response
    {
        $privateKey = setting('tripay_private_key', '', 'payment');
        if ($privateKey === '') {
            Log::warning('Webhook Tripay ditolak: private key belum dikonfigurasi.');
            abort(500, 'Payment gateway belum dikonfigurasi.');
        }

        $rawBody    = $request->getContent();
        $expected   = hash_hmac('sha256', $rawBody, $privateKey);

        if ($request->header('X-Callback-Signature') !== $expected) {
            return response('Invalid signature', 403);
        }

        $payload = $request->all();

        // Idempotency: Tripay memakai merchant_ref sebagai order id.
        $orderId = $payload['merchant_ref'] ?? '';
        if ($orderId !== '' && $this->alreadyPaid($orderId)) {
            return response('OK', 200);
        }

        $this->gateway->handleCallback('tripay', $payload);
        return response('OK', 200);
    }

    private function alreadyPaid(string $orderId): bool
    {
        return PaymentTransaction::where('order_id', $orderId)
            ->where('status', 'success')
            ->exists();
    }
}
