<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to_number', 20);
            $table->string('to_name')->nullable();
            $table->text('message');
            $table->enum('type', ['invoice', 'reminder', 'overdue', 'blast', 'welcome', 'checklist', 'maintenance', 'custom'])->default('custom');
            $table->morphs('notifiable');  // polymorphic: Invoice, Lease, etc.
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->text('error_message')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->timestamps();
            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
