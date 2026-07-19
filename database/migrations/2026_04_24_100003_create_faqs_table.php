<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->text('answer');
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
        Schema::dropIfExists('faqs');
    }
};
