<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('status', ['new', 'read', 'replied'])->default('new');
            $table->text('reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->bigInteger('tenant_id')->unsigned()->nullable(); // SaaS ready
            $table->timestamps();
            $table->index(['status', 'tenant_id']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_submissions');
    }
};
