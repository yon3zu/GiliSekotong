<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Peran baru "pemilik" (pemilik kapal). Pemilik login dengan nomor HP, jadi email boleh kosong.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('pemesan')->change();
            $table->string('email')->nullable()->change();
            $table->index('phone');
        });

        Schema::table('boats', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('slug')->constrained('users')->nullOnDelete();
        });

        // Tanggal kapal tidak bisa dipesan online karena sudah dipakai tamu offline (dilaporkan pemilik/admin).
        Schema::create('boat_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boat_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('reason', 20)->default('tamu_offline'); // tamu_offline | perbaikan | lainnya
            $table->unsignedTinyInteger('passengers')->nullable();
            $table->unsignedInteger('amount')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['boat_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boat_blocks');
        Schema::table('boats', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['phone']);
        });
    }
};
