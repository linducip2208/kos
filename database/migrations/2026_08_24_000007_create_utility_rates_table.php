<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tarif utilitas berjenjang: global → property → room_type → room.
 * Resolusi tarif memakai prioritas paling spesifik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_rates', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 20)->index();                  // global | property | room_type | room
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('utility_type', 30)->index();           // electricity | water | other:<name>
            $table->decimal('rate_per_unit', 12, 4);
            $table->decimal('fixed_charge', 15, 2)->default(0);
            $table->decimal('admin_charge', 15, 2)->default(0);
            $table->decimal('minimum_charge', 15, 2)->default(0);
            $table->decimal('minimum_usage', 12, 2)->default(0);
            $table->date('effective_from')->default(now());
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_rates');
    }
};
