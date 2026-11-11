<?php

namespace App\Services\Bmkg;

use App\Models\BmkgAlert;
use App\Models\BmkgEarthquake;
use App\Models\BmkgForecast;
use App\Models\BmkgSync;

/** Data BMKG yang sudah tersimpan, siap ditampilkan. Tidak pernah memanggil API. */
class BmkgFeed
{
    public function forecastByDay()
    {
        return BmkgForecast::query()
            ->where('local_datetime', '>=', now()->subHours(3))
            ->orderBy('local_datetime')
            ->get()
            ->groupBy(fn ($f) => $f->local_datetime->toDateString());
    }

    /** Slot prakiraan yang paling dekat dengan jam sekarang. */
    public function current(): ?BmkgForecast
    {
        // Pilih di PHP agar tidak bergantung fungsi tanggal khusus MySQL.
        return BmkgForecast::query()
            ->whereBetween('local_datetime', [now()->subHours(3), now()->addHours(6)])
            ->get()
            ->sortBy(fn ($f) => abs($f->local_datetime->diffInMinutes(now())))
            ->first();
    }

    public function activeAlerts()
    {
        return BmkgAlert::query()->active()->orderByDesc('published_at')->get();
    }

    public function recentAlerts(int $days = 7)
    {
        return BmkgAlert::query()->where('published_at', '>=', now()->subDays($days))->orderByDesc('published_at')->get();
    }

    /** 15 gempa terakhir sesuai daftar BMKG. */
    public function latestQuakes()
    {
        return BmkgEarthquake::query()->orderByDesc('occurred_at')->take(15)->get();
    }

    public function nearbyQuakes()
    {
        return $this->latestQuakes()->where('is_nearby', true)->values();
    }

    public function syncs()
    {
        return BmkgSync::all()->keyBy('source');
    }

    public function lastUpdated(): ?\Illuminate\Support\Carbon
    {
        return BmkgSync::query()->max('last_success_at') ? \Illuminate\Support\Carbon::parse(BmkgSync::query()->max('last_success_at')) : null;
    }
}
