<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('occupant_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['check_in', 'check_out']);
            $table->json('items')->nullable();         // [{"label":"Kunci","condition":"baik","notes":""}]
            $table->json('photos')->nullable();
            $table->decimal('damage_cost', 15, 2)->default(0);
            $table->decimal('deposit_refund', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('signed_by')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_checklists');
    }
};
