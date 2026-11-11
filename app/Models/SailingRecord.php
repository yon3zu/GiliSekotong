<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SailingRecord extends Model
{
    protected $fillable = [
        'recorded_on', 'cuaca', 'angin', 'gelombang', 'jarak_pandang', 'pasang_surut', 'keputusan', 'source',
    ];

    protected function casts(): array
    {
        return ['recorded_on' => 'date'];
    }
}
