<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Developer extends Model
{
    public const KINDS = ['dosen' => 'Dosen', 'mahasiswa' => 'Mahasiswa'];

    /** Selalu tampil paling awal di barisan mahasiswa dan tidak bisa diubah dari panel, kecuali fotonya. */
    public const LEAD = ['name' => 'Bayu Jumhari Cahyono S', 'tag' => 'Full Stack Web Developer'];

    protected $fillable = ['name', 'tag', 'kind', 'photo', 'sort', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeShown(Builder $query, string $kind): void
    {
        $query->where('kind', $kind)->where('is_active', true)->orderBy('sort')->orderBy('id');
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }
}
