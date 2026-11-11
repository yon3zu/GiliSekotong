<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BmkgAlert extends Model
{
    protected $fillable = ['guid', 'title', 'description', 'link', 'areas', 'starts_at', 'expires_at', 'published_at', 'impact'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'expires_at' => 'datetime', 'published_at' => 'datetime'];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function impactLabel(): string
    {
        return ['tidak_layak' => 'Dekat rute: tidak layak', 'waspada' => 'Lombok: waspada', 'info' => 'Informasi NTB'][$this->impact] ?? $this->impact;
    }
}
