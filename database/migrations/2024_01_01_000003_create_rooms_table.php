<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kamar individual — setiap kamar bisa punya harga & deskripsi berbeda
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('room_number', 20);
            $table->string('name')->nullable();                      // misal "Kamar Deluxe A1"
            $table->tinyInteger('floor')->nullable();
            $table->text('description')->nullable();                 // deskripsi unik per kamar
            $table->json('facilities')->nullable();                  // override fasilitas per kamar
            $table->json('photos')->nullable();                      // foto spesifik kamar
            $table->decimal('price_monthly', 15, 2)->nullable();    // override harga bulanan
            $table->decimal('price_quarterly', 15, 2)->nullable();  // override harga triwulan
            $table->decimal('price_yearly', 15, 2)->nullable();     // override harga tahunan
            $table->decimal('size_sqm', 8, 2)->nullable();
            $table->enum('status', ['available', 'occupied', 'maintenance', 'reserved'])->default('available');
            $table->date('last_cleaned_at')->nullable();
            $table->text('notes')->nullable();                       // catatan internal
            $table->boolean('is_active')->default(true);
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->timestamps();
            $table->unique(['property_id', 'room_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
