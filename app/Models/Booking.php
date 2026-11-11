<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    public const STATUS_UNPAID = 'menunggu_pembayaran';
    public const STATUS_REVIEW = 'menunggu_verifikasi';
    public const STATUS_CONFIRMED = 'terkonfirmasi';
    public const STATUS_DONE = 'selesai';
    public const STATUS_CANCELLED = 'dibatalkan';

    public const STATUSES = [
        self::STATUS_UNPAID => 'Menunggu Pembayaran',
        self::STATUS_REVIEW => 'Menunggu Verifikasi',
        self::STATUS_CONFIRMED => 'Terkonfirmasi',
        self::STATUS_DONE => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    /** Harga sewa perahu sama untuk kedua jenis trip. */
    public const TRIP_TYPES = [
        'gili' => 'Trip 3 Gili',
        'mancing' => 'Sewa Mancing',
    ];

    public const DEPARTURE_TIMES = ['07:30', '08:30', '09:30', '10:30'];

    protected $fillable = [
        'code', 'user_id', 'boat_id', 'trip_type', 'trip_date', 'departure_time', 'passengers',
        'contact_name', 'contact_phone', 'notes', 'trip_price',
        'meal_total', 'rental_total', 'total', 'unique_code', 'status', 'payment_method', 'payment_proof',
        'qris_order_id', 'qris_status', 'qris_url', 'qris_amount', 'qris_fee', 'qris_fee_payer', 'qris_created_at',
        'paid_at', 'verified_at', 'verified_by', 'checked_in_at', 'completed_at', 'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'trip_date' => 'date',
            'paid_at' => 'datetime',
            'qris_created_at' => 'datetime',
            'verified_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if ($booking->status === null || $booking->status === self::STATUS_UNPAID) {
                $booking->unique_code = self::pickUniqueCode((int) $booking->total);
            }
        });
    }

    /** Kode 1-999 agar nominal transfer berbeda dari pesanan lain yang masih menunggu pembayaran. */
    public static function pickUniqueCode(int $total): int
    {
        $used = static::query()
            ->whereIn('status', [self::STATUS_UNPAID, self::STATUS_REVIEW])
            ->where('total', $total)
            ->pluck('unique_code')
            ->all();
        $free = array_diff(range(1, 999), $used);

        return $free ? $free[array_rand($free)] : random_int(1, 999);
    }

    /** Nominal yang harus ditransfer. Kode unik tidak termasuk pendapatan; total tetap harga pesanan. */
    public function transferAmount(): int
    {
        return $this->total + $this->unique_code;
    }

    public function uniqueCodeLabel(): string
    {
        return str_pad((string) $this->unique_code, 3, '0', STR_PAD_LEFT);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public static function generateCode(): string
    {
        do {
            $code = 'GS'.now()->format('ymd').strtoupper(Str::random(4));
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function boat(): BelongsTo
    {
        return $this->belongsTo(Boat::class);
    }

    public function meals(): HasMany
    {
        return $this->hasMany(BookingMeal::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(BookingRental::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function seaCondition(): BelongsTo
    {
        return $this->belongsTo(SeaCondition::class, 'trip_date', 'date');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Simpan rincian paket makan & sewa alat dari hasil BookingQuote::make(). */
    public function saveLines(array $quote): void
    {
        foreach ($quote['meals'] as $line) {
            $this->meals()->create(['meal_package_id' => $line['id'], ...\Illuminate\Support\Arr::only($line, ['name', 'qty', 'price', 'subtotal'])]);
        }
        foreach ($quote['rentals'] as $line) {
            $this->rentals()->create(['rental_item_id' => $line['id'], ...\Illuminate\Support\Arr::only($line, ['name', 'qty', 'price', 'subtotal'])]);
        }
    }

    public function tripLabel(): string
    {
        return self::TRIP_TYPES[$this->trip_type] ?? $this->trip_type;
    }

    public function canUploadProof(): bool
    {
        return in_array($this->status, [self::STATUS_UNPAID, self::STATUS_REVIEW], true);
    }

    public function hasTicket(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_DONE], true);
    }

    public function markCompleted(): void
    {
        $this->update(['status' => self::STATUS_DONE, 'completed_at' => now()]);
    }

    /** Pemesan boleh menyelesaikan sendiri setelah jam berangkat, kecuali laut dinilai tidak layak. */
    public function canBeCompletedByCustomer(): bool
    {
        return $this->status === self::STATUS_CONFIRMED
            && $this->trip_date->copy()->setTimeFromTimeString($this->departure_time)->isPast()
            && ! SeaCondition::forDate($this->trip_date->toDateString())->blocksBooking();
    }
}
