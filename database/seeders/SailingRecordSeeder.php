<?php

namespace Database\Seeders;

use App\Models\SailingRecord;
use App\Support\Sailing;
use Illuminate\Database\Seeder;

/**
 * DATA CONTOH untuk uji coba sistem.
 * Untuk skripsi, ganti dengan data riwayat pelayaran asli (catatan nakhoda/
 * syahbandar + data BMKG Maritim) lewat menu Data Latih → Impor CSV.
 */
class SailingRecordSeeder extends Seeder
{
    public function run(): void
    {
        if (SailingRecord::query()->exists()) {
            return;
        }

        mt_srand(2026);
        $date = now()->subDays(400);
        $rows = [];

        for ($i = 0; $i < 240; $i++) {
            $date = $date->copy()->addDays(mt_rand(1, 2));
            $wet = in_array((int) $date->format('n'), [12, 1, 2, 3], true); // musim barat

            $sample = [
                'cuaca' => $this->pick($wet ? [25, 30, 30, 15] : [55, 30, 12, 3], Sailing::ATTRIBUTES['cuaca']['values']),
                'angin' => $this->pick($wet ? [35, 40, 25] : [60, 32, 8], Sailing::ATTRIBUTES['angin']['values']),
                'gelombang' => $this->pick($wet ? [15, 35, 35, 15] : [40, 42, 15, 3], Sailing::ATTRIBUTES['gelombang']['values']),
                'jarak_pandang' => $this->pick($wet ? [45, 35, 20] : [75, 20, 5], Sailing::ATTRIBUTES['jarak_pandang']['values']),
                'pasang_surut' => $this->pick([33, 40, 27], Sailing::ATTRIBUTES['pasang_surut']['values']),
            ];

            $rows[] = [
                'recorded_on' => $date->toDateString(),
                ...$sample,
                'keputusan' => $this->noisy($this->decide($sample)),
                'source' => 'contoh',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        SailingRecord::query()->insert($rows);
        mt_srand();
    }

    /** Pola keputusan nakhoda yang disimulasikan. */
    private function decide(array $s): string
    {
        if ($s['gelombang'] === 'Tinggi') {
            return Sailing::TIDAK_LAYAK;
        }
        if ($s['angin'] === 'Kencang') {
            return in_array($s['gelombang'], ['Sedang', 'Rendah'], true) ? Sailing::TIDAK_LAYAK : Sailing::WASPADA;
        }
        if ($s['cuaca'] === 'Hujan Lebat') {
            return $s['jarak_pandang'] === 'Baik' ? Sailing::WASPADA : Sailing::TIDAK_LAYAK;
        }
        if ($s['gelombang'] === 'Sedang') {
            return $s['pasang_surut'] === 'Surut' && $s['angin'] === 'Sedang' ? Sailing::TIDAK_LAYAK : Sailing::WASPADA;
        }
        if ($s['jarak_pandang'] === 'Buruk' || ($s['cuaca'] === 'Hujan Ringan' && $s['angin'] === 'Sedang')) {
            return Sailing::WASPADA;
        }

        return Sailing::LAYAK;
    }

    /** Sekitar 6% keputusan berbeda dari pola, meniru penilaian manusia yang tidak selalu konsisten. */
    private function noisy(string $label): string
    {
        if (mt_rand(1, 100) > 6) {
            return $label;
        }

        return $label === Sailing::WASPADA
            ? (mt_rand(0, 1) ? Sailing::LAYAK : Sailing::TIDAK_LAYAK)
            : Sailing::WASPADA;
    }

    private function pick(array $weights, array $values): string
    {
        $r = mt_rand(1, array_sum($weights));
        foreach ($weights as $i => $w) {
            if (($r -= $w) <= 0) {
                return $values[$i];
            }
        }

        return end($values);
    }
}
