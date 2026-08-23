<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pembayaran manual/gateway per invoice — mendukung partial payment,
 * verifikasi bukti transfer, refund, dan rekonsiliasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('payment');       // payment | refund
            $table->decimal('amount', 15, 2);
            $table->string('method', 30)->default('cash');        // cash | transfer | gateway | qris | va | other
            $table->string('reference', 120)->nullable();         // no. transfer / order id
            $table->string('proof_path')->nullable();             // bukti transfer
            $table->timestamp('paid_at')->nullable()->index();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('status', 30)->default('verified')->index(); // pending_verification | verified | rejected
            $table->string('rejection_reason')->nullable();
            $table->text('reason')->nullable();                   // alasan refund
            $table->foreignId('payment_transaction_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
