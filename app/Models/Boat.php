<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Boat extends Model
{
    /** Batas muatan perahu katir untuk trip gili/mancing. */
    public const MAX_CAPACITY = 12;

    protected $fillable = [
        'name', 'slug', 'owner_id', 'type', 'capacity', 'length_m', 'engine', 'captain',
        'built_year', 'facilities', 'description', 'photo', 'trip_price', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'is_active' => 'boolean',
            'length_m' => 'float',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(BoatBlock::class);
    }

    /** Tanggal ditutup pemilik/admin karena tamu offline atau perbaikan. */
    public function blockOn(string $date): ?BoatBlock
    {
        return $this->blocks()->active()->whereDate('date', $date)->first();
    }

    /** Tidak bisa dipesan bila sudah ada pesanan online aktif atau ditutup karena tamu offline. */
    public function isBookedOn(string $date, ?int $exceptBookingId = null): bool
    {
        return $this->hasOnlineBookingOn($date, $exceptBookingId) || $this->blockOn($date) !== null;
    }

    public function hasOnlineBookingOn(string $date, ?int $exceptBookingId = null): bool
    {
        return $this->bookings()
            ->whereDate('trip_date', $date)
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->when($exceptBookingId, fn ($q) => $q->where('id', '!=', $exceptBookingId))
            ->exists();
    }

    public function visibleReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_visible', true);
    }

    /** Tambahkan visible_reviews_count dan visible_reviews_avg_rating. */
    public function scopeWithRating(Builder $query): void
    {
        $query->withCount('visibleReviews')->withAvg('visibleReviews', 'rating');
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }
}
