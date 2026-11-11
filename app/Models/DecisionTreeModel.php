<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DecisionTreeModel extends Model
{
    protected $fillable = [
        'algorithm', 'tree', 'metrics', 'rules', 'training_size', 'testing_size', 'accuracy', 'is_active', 'trained_by',
    ];

    protected function casts(): array
    {
        return [
            'tree' => 'array',
            'metrics' => 'array',
            'rules' => 'array',
            'is_active' => 'boolean',
            'accuracy' => 'float',
        ];
    }

    public static function active(): ?self
    {
        return static::query()->where('is_active', true)->latest()->first();
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trained_by');
    }
}
