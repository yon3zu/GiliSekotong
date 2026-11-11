<?php

namespace Database\Seeders;

use App\Models\Developer;
use Illuminate\Database\Seeder;

/** Data contoh halaman Tim Pengembang. Tidak dipanggil ProductionSeeder; ganti dengan nama asli lewat Admin → Tim Pengembang. */
class DeveloperSeeder extends Seeder
{
    public function run(): void
    {
        $people = [
            ['Dr. Dosen Contoh Satu, M.Kom', 'Dosen Pembimbing 1', 'dosen', 1],
            ['Dosen Contoh Dua, M.Kom', 'Dosen Pembimbing 2', 'dosen', 2],
            ['Mahasiswa Contoh Satu', 'UI/UX Designer', 'mahasiswa', 1],
            ['Mahasiswa Contoh Dua', 'Backend Developer', 'mahasiswa', 2],
            ['Mahasiswa Contoh Tiga', 'Quality Assurance', 'mahasiswa', 3],
            ['Mahasiswa Contoh Empat', 'Dokumentasi', 'mahasiswa', 4],
        ];

        foreach ($people as [$name, $tag, $kind, $sort]) {
            Developer::query()->updateOrCreate(['name' => $name], ['tag' => $tag, 'kind' => $kind, 'sort' => $sort, 'is_active' => true]);
        }
    }
}
