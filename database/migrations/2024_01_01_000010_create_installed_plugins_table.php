<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installed_plugins', function (Blueprint $table) {
            $table->id();
            $table->string('plugin_slug', 100)->unique();
            $table->string('version', 20);
            $table->string('activation_key', 255)->nullable();
            $table->string('checksum', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installed_plugins');
    }
};
