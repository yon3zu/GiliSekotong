<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BmkgForecast extends Model
{
    protected $fillable = [
        'local_datetime', 'utc_datetime', 'weather_code', 'weather_desc', 'temperature', 'humidity',
        'cloud_cover', 'precipitation', 'wind_kmh', 'wind_dir', 'wind_deg', 'visibility', 'analysis_date',
    ];

    protected function casts(): array
    {
        return [
            'local_datetime' => 'datetime',
            'utc_datetime' => 'datetime',
            'analysis_date' => 'datetime',
            'temperature' => 'float',
            'wind_kmh' => 'float',
            'precipitation' => 'float',
        ];
    }

    public function windKnot(): float
    {
        return round($this->wind_kmh / 1.852, 1);
    }

    /** Arah angin BMKG (E, SE, ...) dalam bahasa Indonesia. */
    public function windDirLabel(): string
    {
        return [
            'N' => 'Utara', 'NE' => 'Timur Laut', 'E' => 'Timur', 'SE' => 'Tenggara',
            'S' => 'Selatan', 'SW' => 'Barat Daya', 'W' => 'Barat', 'NW' => 'Barat Laut',
        ][$this->wind_dir] ?? ($this->wind_dir ?? '—');
    }
}
