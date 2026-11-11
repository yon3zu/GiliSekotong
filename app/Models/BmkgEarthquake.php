<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BmkgEarthquake extends Model
{
    protected $fillable = ['key', 'occurred_at', 'magnitude', 'depth_km', 'lat', 'lon', 'region', 'felt', 'distance_km', 'is_nearby', 'at_sea'];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'magnitude' => 'float',
            'lat' => 'float',
            'lon' => 'float',
            'is_nearby' => 'boolean',
            'at_sea' => 'boolean',
        ];
    }
}
