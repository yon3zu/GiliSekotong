<?php

namespace App\Support;

/**
 * Atribut dan kategori kondisi pelayaran yang dipakai pohon keputusan.
 * Nilai numerik (angin & gelombang) didiskretisasi mengikuti acuan
 * skala Beaufort dan kategori tinggi gelombang BMKG.
 */
class Sailing
{
    public const LAYAK = 'Layak';
    public const WASPADA = 'Waspada';
    public const TIDAK_LAYAK = 'Tidak Layak';

    public const DECISIONS = [self::LAYAK, self::WASPADA, self::TIDAK_LAYAK];

    public const TARGET = 'keputusan';

    public const ATTRIBUTES = [
        'cuaca' => [
            'label' => 'Cuaca',
            'values' => ['Cerah', 'Berawan', 'Hujan Ringan', 'Hujan Lebat'],
        ],
        'angin' => [
            'label' => 'Kecepatan Angin',
            'values' => ['Lemah', 'Sedang', 'Kencang'],
            'hint' => 'Lemah < 11 knot · Sedang 11–21 knot · Kencang > 21 knot',
        ],
        'gelombang' => [
            'label' => 'Tinggi Gelombang',
            'values' => ['Tenang', 'Rendah', 'Sedang', 'Tinggi'],
            'hint' => 'Tenang < 0,5 m · Rendah 0,5–1,25 m · Sedang 1,25–2,5 m · Tinggi > 2,5 m',
        ],
        'jarak_pandang' => [
            'label' => 'Jarak Pandang',
            'values' => ['Baik', 'Sedang', 'Buruk'],
            'hint' => 'Baik > 5 km · Sedang 1–5 km · Buruk < 1 km',
        ],
        'pasang_surut' => [
            'label' => 'Pasang Surut',
            'values' => ['Pasang', 'Normal', 'Surut'],
        ],
    ];

    /** Pilih keputusan yang paling berat (Layak < Waspada < Tidak Layak). */
    public static function worst(?string ...$decisions): ?string
    {
        $decisions = array_filter($decisions);
        if (! $decisions) {
            return null;
        }
        usort($decisions, fn ($a, $b) => array_search($b, self::DECISIONS) <=> array_search($a, self::DECISIONS));

        return $decisions[0];
    }

    /** Nilai kategori paling buruk; urutan nilai di ATTRIBUTES dari terbaik ke terburuk. */
    public static function worstCategory(string $attribute, array $values): ?string
    {
        $order = self::ATTRIBUTES[$attribute]['values'];
        usort($values, fn ($a, $b) => array_search($b, $order) <=> array_search($a, $order));

        return $values[0] ?? null;
    }

    public static function attributeKeys(): array
    {
        return array_keys(self::ATTRIBUTES);
    }

    public static function label(string $attribute): string
    {
        return self::ATTRIBUTES[$attribute]['label'] ?? ucfirst(str_replace('_', ' ', $attribute));
    }

    public static function windCategory(float $knot): string
    {
        return match (true) {
            $knot < 11 => 'Lemah',
            $knot <= 21 => 'Sedang',
            default => 'Kencang',
        };
    }

    public static function waveCategory(float $meter): string
    {
        return match (true) {
            $meter < 0.5 => 'Tenang',
            $meter <= 1.25 => 'Rendah',
            $meter <= 2.5 => 'Sedang',
            default => 'Tinggi',
        };
    }

    public static function tone(?string $decision): string
    {
        return match ($decision) {
            self::LAYAK => 'layak',
            self::WASPADA => 'waspada',
            self::TIDAK_LAYAK => 'tidak',
            default => 'none',
        };
    }

    public static function advice(?string $decision): string
    {
        return match ($decision) {
            self::LAYAK => 'Laut bersahabat. Perjalanan ke tiga gili berjalan sesuai jadwal.',
            self::WASPADA => 'Perjalanan tetap jalan dengan pengawasan nakhoda. Wajib pelampung, rute bisa disesuaikan.',
            self::TIDAK_LAYAK => 'Pelayaran ditunda demi keselamatan. Tanggal ini ditutup, pesanan yang sudah ada dijadwalkan ulang tanpa biaya.',
            default => 'Kondisi laut untuk tanggal ini belum diperbarui oleh petugas dermaga.',
        };
    }
}
