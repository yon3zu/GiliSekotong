<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dosen & mahasiswa di halaman Pengembang. Web developer utama tidak disimpan di sini (lihat Developer::LEAD).
        Schema::create('developers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('tag', 60)->nullable();
            $table->string('kind', 10);                 // dosen | mahasiswa
            $table->string('photo')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['kind', 'is_active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developers');
    }
};
