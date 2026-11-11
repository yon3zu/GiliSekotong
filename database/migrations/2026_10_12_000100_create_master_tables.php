<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('Perahu Katir');
            $table->unsignedTinyInteger('capacity');
            $table->decimal('length_m', 4, 1)->nullable();
            $table->string('engine')->nullable();
            $table->string('captain')->nullable();
            $table->unsignedSmallInteger('built_year')->nullable();
            $table->json('facilities')->nullable();
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedInteger('trip_price')->default(350000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('meal_packages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->json('menu');
            $table->unsignedInteger('price');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Alat yang disewakan per pcs: pelampung, kacamata snorkel, fins.
        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedTinyInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('rental_items');
        Schema::dropIfExists('meal_packages');
        Schema::dropIfExists('boats');
    }
};
