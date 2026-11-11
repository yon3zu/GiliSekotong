<?php

namespace App\Models;

use App\Support\Sailing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeaCondition extends Model
{
    protected $fillable = [
        'date', 'source', 'cuaca', 'wind_knot', 'wave_m', 'jarak_pandang', 'pasang_surut', 'angin', 'gelombang',
        'prediction', 'bmkg_flag', 'bmkg_reasons', 'decision_path', 'model_id', 'final_decision', 'note', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'decision_path' => 'array',
            'bmkg_reasons' => 'array',
            'wind_knot' => 'float',
            'wave_m' => 'float',
        ];
    }

    /** Ambil data tanggal tertentu atau buat baru (whereDate agar aman lintas DB). */
    public static function forDate(string $date): self
    {
        return static::query()->whereDate('date', $date)->first() ?? new static(['date' => $date]);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(DecisionTreeModel::class, 'model_id');
    }

    /**
     * Keputusan yang berlaku:
     * 1. keputusan akhir admin/nakhoda bila ada;
     * 2. selain itu yang paling berat antara prediksi pohon keputusan dan aturan keselamatan BMKG
     *    (peringatan dini cuaca & gempa).
     */
    public function decision(): ?string
    {
        return $this->final_decision ?: Sailing::worst($this->prediction, $this->bmkg_flag);
    }

    /** Peringatan BMKG yang membuat keputusan lebih berat dari prediksi pohon. */
    public function raisedByBmkg(): bool
    {
        return ! $this->final_decision && $this->bmkg_flag && Sailing::worst($this->prediction, $this->bmkg_flag) !== $this->prediction;
    }

    public function isOverridden(): bool
    {
        return $this->final_decision !== null && $this->final_decision !== Sailing::worst($this->prediction, $this->bmkg_flag);
    }

    public function blocksBooking(): bool
    {
        return $this->decision() === Sailing::TIDAK_LAYAK;
    }
}
