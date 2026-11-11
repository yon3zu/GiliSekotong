<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan `php artisan schedule:work` (atau cron `* * * * * php artisan schedule:run`).
// Jeda per sumber tetap diatur config/bmkg.php, jadi menjalankan tiap 10 menit aman.
Illuminate\Support\Facades\Schedule::command('bmkg:sync')->everyTenMinutes()->withoutOverlapping();
Illuminate\Support\Facades\Schedule::command('bookings:complete')->hourly()->withoutOverlapping();
Illuminate\Support\Facades\Schedule::command('qris:sync')->everyMinute()->withoutOverlapping();
