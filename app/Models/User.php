<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = [
        'superadmin' => 'Super Admin',
        'admin' => 'Admin',
        'pemilik' => 'Pemilik Kapal',
        'pemesan' => 'Pemesan',
    ];

    protected $fillable = ['name', 'email', 'phone', 'role', 'is_active', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** Kapal milik user ini (khusus peran pemilik). */
    public function boats(): HasMany
    {
        return $this->hasMany(Boat::class, 'owner_id');
    }

    public function isOwner(): bool
    {
        return $this->role === 'pemilik';
    }

    /** Halaman awal sesuai peran. */
    public function homeRoute(): string
    {
        return match (true) {
            $this->isStaff() => route('admin.dashboard'),
            $this->isOwner() => route('owner.home'),
            default => route('orders.index'),
        };
    }

    /** 0812-3456 7890 / +62812… → 081234567890 */
    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }

        return str_starts_with($digits, '62') ? '0'.substr($digits, 2) : $digits;
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['superadmin', 'admin'], true);
    }

    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }
}
