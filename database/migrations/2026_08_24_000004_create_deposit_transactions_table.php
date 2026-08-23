<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ledger deposit: setiap mutasi (terima, potong, refund, hangus)
 * tercatat dengan saldo berjalan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deposit_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);                           // receipt | deduction | refund | forfeit | adjustment
            $table->decimal('amount', 15, 2);
            $table->text('reason')->nullable();
            $table->string('method', 30)->nullable();             // cara bayar/refund
            $table->string('reference', 120)->nullable();
            $table->nullableMorphs('source');                     // invoice/checklist/maintenance terkait
            $table->foreignId('recorded_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->date('occurred_at')->index();
            $table->decimal('balance_after', 15, 2);
            $table->timestamps();

            $table->index(['deposit_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_transactions');
    }
};
