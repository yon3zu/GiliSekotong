<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\QrisPayment;
use Illuminate\Console\Command;

class SyncQris extends Command
{
    protected $signature = 'qris:sync';

    protected $description = 'Cek status QRIS yang masih menunggu, untuk pemesan yang sudah menutup halaman pesanan';

    /** Batas API 60 request per menit; sisanya dipakai halaman pesanan yang sedang terbuka. */
    private const BATCH = 20;

    public function handle(QrisPayment $qris): int
    {
        Booking::query()
            ->where('qris_status', 'pending')
            ->where('qris_created_at', '>=', now()->subDays(2))
            ->oldest('updated_at')
            ->take(self::BATCH)
            ->get()
            ->each(fn (Booking $booking) => $qris->refresh($booking));

        return self::SUCCESS;
    }
}
