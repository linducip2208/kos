<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat pindah kamar + record check-in / check-out.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Pindah kamar ─────────────────────────────────────────────────
        Schema::create('room_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('to_room_id')->constrained('rooms')->cascadeOnDelete();
            $table->date('effective_date');
            $table->decimal('prorate_amount', 15, 2)->default(0);   // + = tenant bayar selisih, − = kredit
            $table->boolean('transfer_deposit')->default(true);
            $table->boolean('final_utility_done')->default(false);
            $table->boolean('inspection_required')->default(true);
            $table->timestamp('inspection_completed_at')->nullable();
            $table->string('status', 20)->default('completed');     // draft | completed | cancelled
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['lease_id', 'effective_date']);
        });

        // ── Check-in / Check-out ─────────────────────────────────────────
        Schema::create('checkin_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('occupant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->index();                    // check_in | check_out
            $table->decimal('meter_electric_prev', 12, 2)->nullable();
            $table->decimal('meter_electric_current', 12, 2)->nullable();
            $table->decimal('meter_water_prev', 12, 2)->nullable();
            $table->decimal('meter_water_current', 12, 2)->nullable();
            $table->json('checklist')->nullable();                  // [{item_id,name,condition,notes}]
            $table->json('photos')->nullable();
            $table->json('missing_items')->nullable();              // [{name,replacement_value}]
            $table->boolean('key_handover')->default(false);
            $table->decimal('damage_amount', 15, 2)->default(0);
            $table->decimal('cleaning_amount', 15, 2)->default(0);
            $table->decimal('unpaid_utility', 15, 2)->default(0);
            $table->decimal('deposit_deduction', 15, 2)->default(0);
            $table->json('settlement')->nullable();                 // breakdown perhitungan akhir
            $table->decimal('tenant_payable', 15, 2)->default(0);   // + tenant bayar / − refund ke tenant
            $table->string('acknowledged_by')->nullable();          // nama tenant yg ttd
            $table->string('acknowledgement_signature')->nullable();// path ttd digital
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('performed_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['lease_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkin_records');
        Schema::dropIfExists('room_transfers');
    }
};
