<?php

namespace Database\Seeders;

use App\Models\Boat;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Akun contoh pemilik kapal. Login: nomor HP + kata sandi "kapal123". */
class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        $owners = [
            ['Amaq Sahnan', '081300000001', ['bintang-tawun']],
            ['Lalu Sapri', '081300000002', ['sinar-nanggu', 'mutiara-sudak']],
            ['Rusdi', '081300000003', ['lumba-kedis', 'pelangi-sekotong']],
        ];

        foreach ($owners as [$name, $phone, $slugs]) {
            $owner = User::query()->where('role', 'pemilik')->where('phone', $phone)->first()
                ?? User::create(['name' => $name, 'phone' => $phone, 'role' => 'pemilik', 'password' => 'kapal123', 'is_active' => true]);

            Boat::query()->whereIn('slug', $slugs)->whereNull('owner_id')->update(['owner_id' => $owner->id]);
        }
    }
}
