<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->string('contract_number', 50)->unique();
            $table->string('template_used')->nullable();
            $table->text('content_html');
            $table->string('owner_signature')->nullable();   // path to signature image
            $table->timestamp('owner_signed_at')->nullable();
            $table->string('occupant_signature')->nullable();
            $table->timestamp('occupant_signed_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->enum('status', ['draft', 'sent', 'owner_signed', 'fully_signed', 'expired'])->default('draft');
            $table->string('sign_token', 64)->nullable();   // token untuk link tanda tangan
            $table->timestamp('sign_token_expires_at')->nullable();
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_contracts');
    }
};
