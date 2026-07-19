<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat transaksi payment gateway — terpisah dari invoice agar audit trail bersih
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained();
            $table->string('gateway', 30);                  // midtrans | tripay | manual
            $table->string('transaction_id')->nullable();   // ID dari gateway
            $table->string('order_id')->unique();           // order ID yang dikirim ke gateway
            $table->decimal('amount', 15, 2);
            $table->string('payment_type')->nullable();     // bank_transfer, qris, gopay, dll
            $table->string('channel')->nullable();          // BCA, BRI, QRIS, dll
            $table->enum('status', ['pending', 'success', 'failed', 'expired', 'refunded'])->default('pending');
            $table->json('gateway_response')->nullable();   // raw response
            $table->string('payment_url')->nullable();      // link bayar untuk dikirim ke penyewa
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'gateway']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
