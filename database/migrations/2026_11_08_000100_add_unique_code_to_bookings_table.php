<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedSmallInteger('unique_code')->default(0)->after('total');
        });

        // Pesanan yang masih menunggu pembayaran ikut mendapat kode unik.
        $used = [];
        DB::table('bookings')->whereIn('status', ['menunggu_pembayaran', 'menunggu_verifikasi'])->orderBy('id')->get(['id', 'total'])
            ->each(function ($booking) use (&$used) {
                $free = array_diff(range(1, 999), $used[$booking->total] ?? []);
                $code = $free[array_rand($free)];
                $used[$booking->total][] = $code;
                DB::table('bookings')->where('id', $booking->id)->update(['unique_code' => $code]);
            });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('unique_code');
        });
    }
};
