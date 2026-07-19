<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lease_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['electricity', 'water', 'gas'])->default('electricity');
            $table->decimal('previous_reading', 10, 2)->default(0);
            $table->decimal('current_reading', 10, 2);
            $table->decimal('usage', 10, 2)->virtualAs('current_reading - previous_reading');
            $table->decimal('rate_per_unit', 10, 2);       // harga per kWh/liter
            $table->decimal('amount', 15, 2);               // total tagihan
            $table->date('reading_date');
            $table->date('billing_period');                 // YYYY-MM-01
            $table->string('photo')->nullable();
            $table->boolean('added_to_invoice')->default(false);
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->timestamps();
            $table->unique(['room_id', 'type', 'billing_period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_readings');
    }
};
