<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('occupant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->json('photos')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'waiting_parts', 'resolved', 'cancelled'])->default('open');
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->nullable();
            $table->text('resolution_notes')->nullable();
            $table->json('resolution_photos')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->timestamps();
            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
