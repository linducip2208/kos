<?php

namespace App\Http\Controllers;

use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentWebhookController extends Controller
{
    public function __construct(private PaymentGatewayService $gateway) {}

    public function midtrans(Request $request): Response
    {
        $payload = $request->all();

        // Verifikasi signature Midtrans
        $serverKey  = setting('midtrans_server_key', '', 'payment');
        $orderId    = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmt   = $payload['gross_amount'] ?? '';
        $expected   = hash('sha512', $orderId . $statusCode . $grossAmt . $serverKey);

        if (($payload['signature_key'] ?? '') !== $expected) {
            return response('Invalid signature', 403);
        }

        $this->gateway->handleCallback('midtrans', $payload);
        return response('OK', 200);
    }

    public function tripay(Request $request): Response
    {
        $privateKey = setting('tripay_private_key', '', 'payment');
        $rawBody    = $request->getContent();
        $expected   = hash_hmac('sha256', $rawBody, $privateKey);

        if ($request->header('X-Callback-Signature') !== $expected) {
            return response('Invalid signature', 403);
        }

        $payload = $request->all();
        $this->gateway->handleCallback('tripay', $payload);
        return response('OK', 200);
    }
}
