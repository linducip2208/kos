<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category', 60)->nullable()->index();     // elektronik, furniture, akses, lainnya
            $table->string('serial_number', 100)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->date('acquired_at')->nullable();
            $table->string('condition', 20)->default('good')->index(); // good | fair | poor | broken | replaced
            $table->decimal('replacement_value', 15, 2)->default(0);
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'condition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_inventory_items');
    }
};
