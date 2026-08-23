<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Log automation engine + pivot assignment property manager.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('rule_key', 80)->index();
            $table->string('channel', 20)->default('database');  // database | whatsapp | email
            $table->nullableMorphs('subject');
            $table->string('recipient', 200)->nullable();
            $table->string('status', 20)->default('triggered')->index(); // triggered | success | failed | skipped
            $table->unsignedInteger('attempts')->default(0);
            $table->text('message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('property_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'property_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_user');
        Schema::dropIfExists('automation_logs');
    }
};
