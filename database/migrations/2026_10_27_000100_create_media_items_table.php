<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Foto & video keindahan gili untuk beranda (hero dan galeri).
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10);                 // image | video
            $table->string('path');
            $table->string('poster_path')->nullable();  // gambar sampul video
            $table->string('title', 120);
            $table->string('location', 40)->nullable(); // Gili Nanggu, Gili Sudak, ...
            $table->string('placement', 10)->default('gallery'); // hero | gallery
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['placement', 'is_active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_items');
    }
};
