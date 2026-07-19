<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        foreach (\App\Models\Property::whereNull('slug')->get() as $property) {
            $property->slug = \Illuminate\Support\Str::slug($property->name);
            $property->save();
        }

        Schema::table('properties', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
