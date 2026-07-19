<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('occupants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20);
            $table->string('whatsapp', 20)->nullable();
            $table->string('id_number', 30)->nullable();
            $table->enum('id_type', ['ktp', 'sim', 'passport'])->default('ktp');
            $table->string('id_photo')->nullable();
            $table->string('selfie_photo')->nullable();
            $table->text('address')->nullable();
            $table->string('occupation')->nullable();
            $table->string('workplace')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->text('notes')->nullable();
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occupants');
    }
};
