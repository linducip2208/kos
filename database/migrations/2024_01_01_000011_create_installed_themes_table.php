<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installed_themes', function (Blueprint $table) {
            $table->id();
            $table->enum('area', ['admin', 'user', 'frontend']);
            $table->string('slug', 100);
            $table->string('name', 150);
            $table->string('version', 20)->default('1.0.0');
            $table->boolean('is_active')->default(false);
            $table->json('settings')->nullable();
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->timestamps();
            $table->unique(['area', 'slug', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installed_themes');
    }
};
