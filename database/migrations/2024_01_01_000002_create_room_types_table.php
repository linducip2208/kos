<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('size_sqm', 8, 2)->nullable();
            $table->decimal('base_price_monthly', 15, 2)->default(0);
            $table->decimal('base_price_quarterly', 15, 2)->nullable();
            $table->decimal('base_price_yearly', 15, 2)->nullable();
            $table->json('facilities')->nullable();
            $table->json('photos')->nullable();
            $table->tinyInteger('max_occupants')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
