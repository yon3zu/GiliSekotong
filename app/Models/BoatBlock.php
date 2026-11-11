<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoatBlock extends Model
{
    public const REASONS = [
        'tamu_offline' => 'Dipakai tamu offline',
        'perbaikan' => 'Kapal diperbaiki',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = ['boat_id', 'date', 'reason', 'passengers', 'amount', 'note', 'reported_by', 'cancelled_at'];

    protected function casts(): array
    {
        return ['date' => 'date', 'cancelled_at' => 'datetime'];
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNull('cancelled_at');
    }

    public function boat(): BelongsTo
    {
        return $this->belongsTo(Boat::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }
}
