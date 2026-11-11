<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\SailingModelTrainer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Data awal untuk server produksi:
 * - satu akun Super Admin dengan password acak (ditampilkan sekali di terminal),
 * - perahu, paket makan, sewa alat, dan pengaturan dasar (MasterSeeder),
 * - data latih contoh + model pohon keputusan awal.
 * Tidak membuat pesanan, pemesan, maupun pemilik kapal contoh.
 *
 *   php artisan db:seed --class=ProductionSeeder --force
 *   ADMIN_EMAIL=nama@gilisekotong.com php artisan db:seed --class=ProductionSeeder --force
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'superadmin@gilisekotong.com');
        $password = Str::password(14, symbols: false);

        $admin = User::query()->where('email', $email)->first();
        if ($admin) {
            $this->command?->warn("Akun {$email} sudah ada; password tidak diubah.");
        } else {
            $admin = User::create([
                'name' => 'Super Admin', 'email' => $email, 'role' => 'superadmin',
                'password' => $password, 'is_active' => true,
            ]);
            $this->command?->newLine();
            $this->command?->info('Akun Super Admin dibuat. Simpan baik-baik, password tidak ditampilkan lagi:');
            $this->command?->line("  Email    : {$email}");
            $this->command?->line("  Password : {$password}");
            $this->command?->newLine();
        }

        $this->call([MasterSeeder::class, SailingRecordSeeder::class]);

        app(SailingModelTrainer::class)->train('C4.5', 0.2, 42, $admin->id, 10);
        $this->command?->info('Model pohon keputusan awal dilatih (data latih contoh, ganti dengan data asli lewat Admin → Data Latih).');
    }
}
