<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\SeaCondition;
use App\Models\Setting;
use Illuminate\Console\Command;

class CompleteBookings extends Command
{
    protected $signature = 'bookings:complete';

    protected $description = 'Tandai selesai pesanan terkonfirmasi yang tanggal tripnya sudah lewat';

    public function handle(): int
    {
        $days = max(1, (int) Setting::get('auto_complete_days'));
        $done = 0;

        $bookings = Booking::query()
            ->where('status', Booking::STATUS_CONFIRMED)
            ->whereDate('trip_date', '<=', today()->subDays($days))
            ->get();

        foreach ($bookings as $booking) {
            // Laut tidak layak berarti trip kemungkinan batal berangkat: biarkan admin menjadwalkan ulang.
            if (SeaCondition::forDate($booking->trip_date->toDateString())->blocksBooking()) {
                continue;
            }
            $booking->markCompleted();
            $done++;
        }

        $this->info("{$done} pesanan ditandai selesai.");

        return self::SUCCESS;
    }
}
