<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->date('desired_move_in');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'contacted', 'approved', 'rejected', 'converted'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('converted_to_lease_id')->nullable()->references('id')->on('leases')->nullOnDelete();
            $table->foreignId('converted_to_occupant_id')->nullable()->references('id')->on('occupants')->nullOnDelete();
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
