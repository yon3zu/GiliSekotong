<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun awal. Ganti kata sandi setelah login pertama.
        $users = [
            ['name' => 'Super Admin', 'email' => 'superadmin@gilisekotong.com', 'role' => 'superadmin', 'phone' => '081200000001'],
            ['name' => 'Admin Dermaga', 'email' => 'admin@gilisekotong.com', 'role' => 'admin', 'phone' => '081200000002'],
            ['name' => 'Rina Pemesan', 'email' => 'pemesan@contoh.id', 'role' => 'pemesan', 'phone' => '081200000003'],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(['email' => $user['email']], [...$user, 'password' => 'password', 'is_active' => true]);
        }
    }
}
