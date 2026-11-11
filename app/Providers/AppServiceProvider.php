<?php

namespace App\Providers;

use App\Support\MailSettings;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pengaturan SMTP dari Admin → Email. Diabaikan bila database belum siap (mis. saat instalasi awal).
        try {
            MailSettings::apply();
        } catch (Throwable) {
            //
        }
    }
}
