<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BmkgSync extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'source';

    protected $keyType = 'string';

    protected $fillable = ['source', 'last_attempt_at', 'last_success_at', 'status', 'message', 'items'];

    protected function casts(): array
    {
        return ['last_attempt_at' => 'datetime', 'last_success_at' => 'datetime'];
    }
}
