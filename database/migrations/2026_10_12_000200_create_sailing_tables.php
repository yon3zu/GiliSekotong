<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Data latih: riwayat kondisi laut + keputusan berlayar yang benar-benar diambil.
        Schema::create('sailing_records', function (Blueprint $table) {
            $table->id();
            $table->date('recorded_on')->nullable();
            $table->string('cuaca', 20);
            $table->string('angin', 20);
            $table->string('gelombang', 20);
            $table->string('jarak_pandang', 20);
            $table->string('pasang_surut', 20);
            $table->string('keputusan', 20);
            $table->string('source', 30)->default('manual');
            $table->timestamps();
        });

        // Model pohon keputusan hasil pelatihan (disimpan sebagai JSON).
        Schema::create('decision_tree_models', function (Blueprint $table) {
            $table->id();
            $table->string('algorithm', 10);
            $table->json('tree');
            $table->json('metrics');
            $table->json('rules');
            $table->unsignedInteger('training_size');
            $table->unsignedInteger('testing_size');
            $table->decimal('accuracy', 5, 2);
            $table->boolean('is_active')->default(false);
            $table->foreignId('trained_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Kondisi laut harian yang diinput admin dan diprediksi oleh model aktif.
        Schema::create('sea_conditions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('cuaca', 20);
            $table->decimal('wind_knot', 4, 1);
            $table->decimal('wave_m', 3, 2);
            $table->string('jarak_pandang', 20);
            $table->string('pasang_surut', 20);
            $table->string('angin', 20);
            $table->string('gelombang', 20);
            $table->string('prediction', 20)->nullable();
            $table->json('decision_path')->nullable();
            $table->foreignId('model_id')->nullable()->constrained('decision_tree_models')->nullOnDelete();
            $table->string('final_decision', 20)->nullable();
            $table->string('note')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sea_conditions');
        Schema::dropIfExists('decision_tree_models');
        Schema::dropIfExists('sailing_records');
    }
};
