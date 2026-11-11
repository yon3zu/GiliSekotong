<?php

namespace App\Services\Bmkg;

use App\Models\BmkgAlert;
use App\Models\BmkgEarthquake;
use App\Models\BmkgForecast;
use App\Models\BmkgSync;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Mengambil data BMKG lalu menyimpannya ke database.
 *
 * Setiap sumber punya jeda minimum (config/bmkg.php → sources.*.interval). Selama jeda belum lewat,
 * tidak ada request ke BMKG; halaman web cukup membaca database. Lock cache mencegah dua request
 * pengunjung memicu sinkronisasi yang sama bersamaan.
 */
class BmkgImporter
{
    public function __construct(private BmkgSafety $safety) {}

    /** @return array<string, string> status per sumber: synced | skipped | failed */
    public function refresh(bool $force = false): array
    {
        $result = [];
        foreach (array_keys(config('bmkg.sources')) as $source) {
            $result[$source] = $force || $this->isDue($source) ? $this->run($source) : 'skipped';
        }

        if (in_array('synced', $result, true)) {
            $this->safety->apply();
        }

        return $result;
    }

    public function isDue(string $source): bool
    {
        $sync = BmkgSync::find($source);
        if (! $sync?->last_attempt_at) {
            return true;
        }

        $wait = $sync->status === 'ok'
            ? config("bmkg.sources.$source.interval")
            : config('bmkg.retry_after_failure');

        return $sync->last_attempt_at->addMinutes($wait)->isPast();
    }

    private function run(string $source): string
    {
        $lock = Cache::lock("bmkg-sync-$source", 60);
        if (! $lock->get()) {
            return 'skipped';
        }

        try {
            $body = $this->fetch(config("bmkg.sources.$source.url"));
            $items = match ($source) {
                'forecast' => $this->importForecast($body),
                'nowcast' => $this->importNowcast($body),
                'earthquake' => $this->importEarthquakes($body),
            };
            $this->record($source, 'ok', "{$items} data diperbarui", $items);

            return 'synced';
        } catch (Throwable $e) {
            Log::warning("Sinkronisasi BMKG [$source] gagal: ".$e->getMessage());
            $this->record($source, 'gagal', mb_strimwidth($e->getMessage(), 0, 250, '…'));

            return 'failed';
        } finally {
            $lock->release();
        }
    }

    private function fetch(string $url): string
    {
        // Server BMKG hanya menerima TLS 1.3. cURL lama (mis. bawaan XAMPP) tidak memilihnya sendiri,
        // jadi dipaksa agar tidak gagal dengan "tlsv1 alert protocol version".
        $response = Http::withUserAgent(config('bmkg.user_agent'))
            ->withOptions(['curl' => [CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_3]])
            ->timeout(config('bmkg.timeout'))
            ->get($url);

        if ($response->status() === 429) {
            throw new RuntimeException('BMKG membatasi permintaan (HTTP 429). Dicoba lagi nanti.');
        }
        if (! $response->successful()) {
            throw new RuntimeException('HTTP '.$response->status());
        }

        return $response->body();
    }

    private function record(string $source, string $status, string $message, int $items = 0): void
    {
        $sync = BmkgSync::firstOrNew(['source' => $source]);
        $sync->fill(['last_attempt_at' => now(), 'status' => $status, 'message' => $message]);
        if ($status === 'ok') {
            $sync->fill(['last_success_at' => now(), 'items' => $items]);
        }
        $sync->save();
    }

    /* ------------------------------------------------------------------ prakiraan cuaca */

    public function importForecast(string $body): int
    {
        $json = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        $slots = array_merge(...($json['data'][0]['cuaca'] ?? [[]]));
        if (! $slots) {
            throw new RuntimeException('Format prakiraan cuaca tidak dikenali.');
        }

        $rows = array_map(fn ($s) => [
            'local_datetime' => $s['local_datetime'],
            'utc_datetime' => $s['utc_datetime'],
            'weather_code' => (int) $s['weather'],
            'weather_desc' => $s['weather_desc'],
            'temperature' => $s['t'],
            'humidity' => $s['hu'],
            'cloud_cover' => $s['tcc'],
            'precipitation' => $s['tp'] ?? 0,
            'wind_kmh' => $s['ws'],
            'wind_dir' => $s['wd'] ?? null,
            'wind_deg' => $s['wd_deg'] ?? null,
            'visibility' => $s['vs_text'] ?? null,
            'analysis_date' => isset($s['analysis_date']) ? Carbon::parse($s['analysis_date'])->toDateTimeString() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $slots);

        BmkgForecast::upsert($rows, ['local_datetime'], array_keys(array_diff_key($rows[0], ['created_at' => 1])));
        // Simpan riwayat 30 hari saja.
        BmkgForecast::where('local_datetime', '<', now()->subDays(30))->delete();

        return count($rows);
    }

    /* ------------------------------------------------------------------ nowcast */

    /** Hanya peringatan yang menyebut Nusa Tenggara Barat yang disimpan. */
    public function importNowcast(string $body): int
    {
        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        libxml_use_internal_errors($previous);
        if ($xml === false || ! isset($xml->channel)) {
            throw new RuntimeException('Format RSS nowcast tidak dikenali.');
        }

        $province = mb_strtolower(config('bmkg.alert_province'));
        $count = 0;

        foreach ($xml->channel->item as $item) {
            $title = trim((string) $item->title);
            $description = trim(preg_replace('/\s+/', ' ', (string) $item->description));

            if (! str_contains(mb_strtolower($title.' '.$description), $province)) {
                continue;
            }

            $areas = preg_match('/khususnya di (.+?)\.(\s|$)/u', $description, $m) ? trim($m[1]) : null;

            BmkgAlert::updateOrCreate(['guid' => (string) $item->guid], [
                'title' => $title,
                'description' => $description,
                'link' => (string) $item->link ?: null,
                'areas' => $areas,
                'starts_at' => self::parseIndonesianTime($description, 'pada'),
                'expires_at' => self::parseIndonesianTime($description, 'hingga'),
                'published_at' => ($pub = (string) $item->pubDate) ? Carbon::parse($pub)->setTimezone(config('app.timezone')) : null,
                'impact' => $this->alertImpact($areas ?? $description),
            ]);
            $count++;
        }

        BmkgAlert::where('created_at', '<', now()->subDays(60))->delete();

        return $count;
    }

    public function alertImpact(string $text): string
    {
        $text = mb_strtoupper($text);
        $has = fn (array $words) => collect($words)->contains(fn ($w) => preg_match('/\b'.preg_quote($w, '/').'\b/u', $text));

        return match (true) {
            $has(config('bmkg.alert_block_areas')) => 'tidak_layak',
            $has(config('bmkg.alert_caution_areas')) => 'waspada',
            default => 'info',
        };
    }

    /** "… pada 16 October 2026, 18:30 WITA …" → Carbon (zona waktu aplikasi). */
    public static function parseIndonesianTime(string $text, string $keyword): ?Carbon
    {
        if (! preg_match('/'.$keyword.' (\d{1,2}) (\p{L}+) (\d{4}),? (\d{1,2})[:.](\d{2}) (WIB|WITA|WIT)/u', $text, $m)) {
            return null;
        }

        $months = [
            'januari' => 1, 'january' => 1, 'februari' => 2, 'february' => 2, 'maret' => 3, 'march' => 3,
            'april' => 4, 'mei' => 5, 'may' => 5, 'juni' => 6, 'june' => 6, 'juli' => 7, 'july' => 7,
            'agustus' => 8, 'august' => 8, 'september' => 9, 'oktober' => 10, 'october' => 10,
            'november' => 11, 'desember' => 12, 'december' => 12,
        ];
        $month = $months[mb_strtolower($m[2])] ?? null;
        if (! $month) {
            return null;
        }

        $tz = ['WIB' => 'Asia/Jakarta', 'WITA' => 'Asia/Makassar', 'WIT' => 'Asia/Jayapura'][$m[6]];

        return Carbon::create((int) $m[3], $month, (int) $m[1], (int) $m[4], (int) $m[5], 0, $tz)->setTimezone(config('app.timezone'));
    }

    /* ------------------------------------------------------------------ gempa dirasakan */

    public function importEarthquakes(string $body): int
    {
        $json = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        $events = $json['Infogempa']['gempa'] ?? null;
        if (! is_array($events)) {
            throw new RuntimeException('Format data gempa tidak dikenali.');
        }

        ['lat' => $refLat, 'lon' => $refLon] = config('bmkg.location');

        foreach ($events as $e) {
            [$lat, $lon] = array_map('floatval', explode(',', $e['Coordinates']));
            $distance = (int) round(self::distanceKm($refLat, $refLon, $lat, $lon));
            $text = mb_strtoupper($e['Wilayah'].' '.($e['Dirasakan'] ?? ''));
            $keyword = collect(config('bmkg.quake_keywords'))->contains(fn ($w) => preg_match('/\b'.preg_quote($w, '/').'\b/u', $text));

            BmkgEarthquake::updateOrCreate(['key' => $e['DateTime'].'|'.$e['Coordinates']], [
                'occurred_at' => Carbon::parse($e['DateTime'])->setTimezone(config('app.timezone')),
                'magnitude' => (float) $e['Magnitude'],
                'depth_km' => (int) $e['Kedalaman'] ?: null,
                'lat' => $lat,
                'lon' => $lon,
                'region' => $e['Wilayah'],
                'felt' => $e['Dirasakan'] ?? null,
                'distance_km' => $distance,
                'is_nearby' => $keyword || $distance <= config('bmkg.quake_radius_km'),
                'at_sea' => str_contains(mb_strtolower($e['Wilayah']), 'di laut'),
            ]);
        }

        BmkgEarthquake::where('occurred_at', '<', now()->subDays(90))->delete();

        return count($events);
    }

    /** Jarak lingkaran besar (haversine) dalam km. */
    public static function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
