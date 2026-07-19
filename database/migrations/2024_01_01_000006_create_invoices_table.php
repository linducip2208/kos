<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained();
            $table->string('invoice_number', 50)->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->date('due_date');
            $table->decimal('base_amount', 15, 2);
            $table->json('additional_charges')->nullable();  // [{"label":"Listrik","amount":50000}]
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->decimal('penalty', 15, 2)->default(0);  // denda keterlambatan
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_ref', 100)->nullable();
            $table->string('payment_channel', 50)->nullable();  // midtrans, tripay, manual
            $table->json('payment_gateway_data')->nullable();   // raw response gateway
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->timestamps();
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
