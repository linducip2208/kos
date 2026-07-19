<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('occupation')->nullable();
            $table->string('avatar')->nullable();
            $table->tinyInteger('rating')->default(5); // 1-5
            $table->text('content');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->bigInteger('tenant_id')->unsigned()->nullable(); // SaaS ready
            $table->timestamps();
            $table->index(['property_id', 'is_active']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
