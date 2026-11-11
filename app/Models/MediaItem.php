<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MediaItem extends Model
{
    public const LOCATIONS = ['Gili Nanggu', 'Gili Sudak', 'Gili Kedis', 'Dermaga Tawun', 'Perjalanan Laut', 'Bawah Laut'];

    public const PLACEMENTS = [
        'hero' => 'Hero beranda (layar penuh paling atas)',
        'gallery' => 'Galeri "Keindahan 3 Gili"',
    ];

    protected $fillable = ['type', 'path', 'poster_path', 'title', 'location', 'placement', 'sort', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeShown(Builder $query, string $placement): void
    {
        $query->where('placement', $placement)->where('is_active', true)->orderBy('sort')->orderBy('id');
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }

    public function posterUrl(): ?string
    {
        return $this->poster_path ? asset('storage/'.$this->poster_path) : null;
    }

    /** Gambar diam yang mewakili item ini (foto itu sendiri atau sampul video). */
    public function stillUrl(): ?string
    {
        return $this->isVideo() ? $this->posterUrl() : $this->url();
    }
}
