<?php

namespace App\Services\Bmkg;

use App\Models\BmkgAlert;
use App\Models\BmkgEarthquake;
use App\Models\BmkgForecast;
use App\Models\SeaCondition;
use App\Services\SailingModelTrainer;
use App\Support\Sailing;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Menghubungkan data BMKG dengan keputusan pelayaran.
 *
 * 1. Prakiraan cuaca BMKG → atribut masukan pohon keputusan (cuaca, angin, jarak pandang,
 *    perkiraan gelombang) untuk tanggal yang belum diisi manual oleh petugas dermaga.
 * 2. Peringatan dini cuaca NTB dan gempa dirasakan di sekitar Lombok → aturan keselamatan
 *    yang dapat menaikkan keputusan (mis. Layak → Tidak Layak), tidak pernah menurunkan.
 */
class BmkgSafety
{
    public function __construct(private SailingModelTrainer $trainer) {}

    public function apply(): void
    {
        foreach ($this->forecastDates() as $date) {
            $this->fillFromForecast($date);
        }

        SeaCondition::query()
            ->whereBetween('date', [today()->toDateString(), today()->addDays(6)->toDateString()])
            ->get()
            ->each(function (SeaCondition $c) {
                [$flag, $reasons] = $this->evaluate($c->date);
                $c->bmkg_flag = $flag;
                $c->bmkg_reasons = $reasons ?: null;
                $c->save();
            });
    }

    /** @return Collection<int, Carbon> */
    private function forecastDates(): Collection
    {
        return BmkgForecast::query()
            ->where('local_datetime', '>=', today())
            ->pluck('local_datetime')
            ->map(fn ($dt) => $dt->copy()->startOfDay())
            ->unique(fn ($d) => $d->toDateString())
            ->values();
    }

    /** Ringkasan prakiraan pada jam operasional perahu: kondisi terburuk yang mungkin dihadapi. */
    public function dailySummary(Carbon $date): ?array
    {
        [$from, $to] = config('bmkg.sailing_hours');
        $slots = BmkgForecast::query()
            ->whereBetween('local_datetime', [$date->copy()->setTime($from, 0), $date->copy()->setTime($to, 0)])
            ->orderBy('local_datetime')
            ->get();

        if ($slots->isEmpty()) {
            return null;
        }

        $cuaca = Sailing::worstCategory('cuaca', $slots->map(fn ($s) => self::weatherCategory($s->weather_code))->all());
        $visibility = Sailing::worstCategory('jarak_pandang', $slots->map(fn ($s) => self::visibilityCategory($s->weather_code, $s->visibility))->all());
        $windKnot = round($slots->max('wind_kmh') / 1.852, 1);

        return [
            'cuaca' => $cuaca,
            'wind_knot' => $windKnot,
            'wave_m' => self::waveFromWind($windKnot),
            'jarak_pandang' => $visibility,
            'slots' => $slots->map(fn ($s) => $s->local_datetime->format('H:i').' '.$s->weather_desc)->all(),
        ];
    }

    private function fillFromForecast(Carbon $date): void
    {
        $condition = SeaCondition::forDate($date->toDateString());
        // Data yang diinput petugas tidak ditimpa.
        if ($condition->exists && $condition->source !== 'bmkg') {
            return;
        }

        $summary = $this->dailySummary($date);
        if (! $summary) {
            return;
        }

        $condition->fill([
            'source' => 'bmkg',
            'cuaca' => $summary['cuaca'],
            'wind_knot' => $summary['wind_knot'],
            'wave_m' => $summary['wave_m'],
            'jarak_pandang' => $summary['jarak_pandang'],
            'pasang_surut' => $condition->pasang_surut ?? 'Normal',
            'angin' => Sailing::windCategory($summary['wind_knot']),
            'gelombang' => Sailing::waveCategory($summary['wave_m']),
            'note' => $condition->note ?? 'Otomatis dari prakiraan BMKG. Gelombang diperkirakan dari kecepatan angin.',
        ]);

        $this->trainer->applyPrediction($condition)->save();
    }

    /**
     * Aturan keselamatan BMKG untuk satu tanggal.
     *
     * @return array{0: ?string, 1: array<int, array<string, string|null>>}
     */
    public function evaluate(Carbon $date): array
    {
        [$from, $to] = config('bmkg.sailing_hours');
        $start = $date->copy()->setTime($from, 0);
        $end = $date->copy()->setTime($to, 0);
        $reasons = [];
        $levels = [];

        $alerts = BmkgAlert::query()->where('impact', '!=', 'info')->get()->filter(function (BmkgAlert $a) use ($start, $end) {
            $alertStart = $a->starts_at ?? $a->published_at;
            $alertEnd = $a->expires_at ?? $alertStart?->copy()->addHours(3);

            return $alertStart && $alertStart <= $end && $alertEnd >= $start;
        });

        foreach ($alerts as $alert) {
            $level = $alert->impact === 'tidak_layak' ? Sailing::TIDAK_LAYAK : Sailing::WASPADA;
            $levels[] = $level;
            $reasons[] = [
                'type' => 'peringatan',
                'level' => $level,
                'text' => $alert->title.($alert->areas ? ' ('.$alert->areas.')' : '').
                    ($alert->expires_at ? ', hingga '.$alert->expires_at->format('H:i').' WITA' : ''),
                'link' => $alert->link,
            ];
        }

        // Gempa di sekitar Lombok dalam 24 jam sebelum jam berangkat sampai akhir jam operasional.
        $quakes = BmkgEarthquake::query()
            ->where('is_nearby', true)
            ->where('magnitude', '>=', config('bmkg.quake_caution_magnitude'))
            ->whereBetween('occurred_at', [$start->copy()->subDay(), $end])
            ->get();

        foreach ($quakes as $q) {
            $level = $q->at_sea && $q->magnitude >= config('bmkg.quake_block_magnitude') ? Sailing::TIDAK_LAYAK : Sailing::WASPADA;
            $levels[] = $level;
            $reasons[] = [
                'type' => 'gempa',
                'level' => $level,
                'text' => "Gempa M{$q->magnitude} {$q->occurred_at->translatedFormat('d M H:i')} WITA, {$q->distance_km} km dari Tawun ({$q->region})",
                'link' => null,
            ];
        }

        return [Sailing::worst(...$levels), $reasons];
    }

    /** Kode cuaca BMKG → kategori atribut "cuaca" pohon keputusan. */
    public static function weatherCategory(int $code): string
    {
        return match (true) {
            in_array($code, [0, 1], true) => 'Cerah',
            in_array($code, [60, 61, 80], true) => 'Hujan Ringan',
            in_array($code, [63, 65, 95, 97], true) => 'Hujan Lebat',
            default => 'Berawan', // 2 cerah berawan, 3 berawan, 4 berawan tebal, 5 udara kabur, 10 asap, 45 kabut
        };
    }

    public static function visibilityCategory(int $code, ?string $text): string
    {
        // vs_text BMKG mis. "> 10 km", "< 1 km", "1 - 5 km"
        if ($text && preg_match_all('/\d+(?:[.,]\d+)?/', $text, $m)) {
            $km = (float) str_replace(',', '.', end($m[0]));
            if (str_contains($text, '<')) {
                $km -= 0.01;
            }

            return $km > 5 ? 'Baik' : ($km >= 1 ? 'Sedang' : 'Buruk');
        }

        return match (true) {
            in_array($code, [63, 65, 95, 97], true) => 'Buruk',
            in_array($code, [5, 10, 45, 60, 61, 80], true) => 'Sedang',
            default => 'Baik',
        };
    }

    /**
     * API prakiraan cuaca BMKG tidak memuat tinggi gelombang, jadi diperkirakan dari kecepatan angin
     * memakai tinggi gelombang tipikal skala Beaufort (laut terbuka, sehingga cenderung konservatif).
     */
    public static function waveFromWind(float $knot): float
    {
        return match (true) {
            $knot < 4 => 0.1,
            $knot < 7 => 0.2,
            $knot < 11 => 0.6,
            $knot < 17 => 1.0,
            $knot < 22 => 2.0,
            $knot < 28 => 3.0,
            default => 4.0,
        };
    }
}
