<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained();
            $table->foreignId('occupant_id')->constrained();
            $table->string('lease_number', 50)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price', 15, 2);
            $table->decimal('deposit', 15, 2)->default(0);
            $table->decimal('deposit_returned', 15, 2)->default(0);
            $table->date('deposit_returned_at')->nullable();
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->tinyInteger('billing_date')->default(1);
            $table->enum('status', ['active', 'expired', 'terminated', 'pending'])->default('pending');
            $table->date('terminated_at')->nullable();
            $table->text('termination_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
