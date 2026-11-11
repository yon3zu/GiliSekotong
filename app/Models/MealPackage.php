<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPackage extends Model
{
    protected $fillable = ['code', 'name', 'menu', 'price', 'is_active'];

    protected function casts(): array
    {
        return [
            'menu' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
