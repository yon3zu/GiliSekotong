<?php

namespace Database\Seeders;

use App\Models\Boat;
use App\Models\MealPackage;
use App\Models\RentalItem;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        Setting::put(Setting::DEFAULTS);

        $boats = [
            [
                'name' => 'Bintang Tawun', 'type' => 'Perahu Katir', 'capacity' => 12, 'length_m' => 8.5,
                'engine' => '1 × Yamaha 15 PK', 'captain' => 'Pak Amaq Sahnan', 'built_year' => 2019,
                'facilities' => ['Atap terpal', '12 pelampung standar', 'Kotak P3K', 'Dudukan joran pancing'],
                'description' => 'Perahu lincah dengan lambung ramping. Cocok untuk trip santai ke gili atau mancing di perairan sekitar Tawun.',
            ],
            [
                'name' => 'Sinar Nanggu', 'type' => 'Perahu Katir', 'capacity' => 12, 'length_m' => 9.5,
                'engine' => '1 × Yamaha 25 PK', 'captain' => 'Pak Lalu Sapri', 'built_year' => 2020,
                'facilities' => ['Atap kayu', '12 pelampung standar', 'Kotak P3K', 'Cool box', 'Dudukan joran pancing'],
                'description' => 'Pilihan paling sering dipesan rombongan teman dan keluarga. Lambung lebar sehingga stabil saat melewati arus antara Gili Nanggu dan Gili Sudak.',
            ],
            [
                'name' => 'Mutiara Sudak', 'type' => 'Perahu Katir', 'capacity' => 12, 'length_m' => 11,
                'engine' => '2 × Yamaha 25 PK', 'captain' => 'Pak Muhammad Zaini', 'built_year' => 2021,
                'facilities' => ['Atap kayu', '12 pelampung standar', 'Kotak P3K', 'Cool box', 'Tangga renang'],
                'description' => 'Bermesin ganda, lebih cepat dan tetap aman bila satu mesin bermasalah. Ada tangga renang di sisi lambung untuk naik turun saat snorkeling.',
            ],
            [
                'name' => 'Lumba Kedis', 'type' => 'Perahu Katir Besar', 'capacity' => 12, 'length_m' => 12.5,
                'engine' => '2 × Yamaha 40 PK', 'captain' => 'Pak Rusdi', 'built_year' => 2022,
                'facilities' => ['Atap kayu lebar', '12 pelampung standar', 'Kotak P3K', 'Cool box', 'Tangga renang', 'Speaker portabel'],
                'description' => 'Untuk rombongan penuh sampai 12 orang. Dek luas, banyak tempat duduk teduh, dan cukup ruang untuk makan siang di atas perahu.',
            ],
            [
                'name' => 'Pelangi Sekotong', 'type' => 'Perahu Katir Besar', 'capacity' => 12, 'length_m' => 13,
                'engine' => '2 × Yamaha 40 PK', 'captain' => 'Pak Hamdan', 'built_year' => 2023,
                'facilities' => ['Atap kayu lebar', '12 pelampung standar', 'Kotak P3K', 'Cool box', 'Tangga renang', 'Toilet portabel', 'Dudukan joran pancing'],
                'description' => 'Dilengkapi toilet portabel dan dudukan joran, jadi nyaman untuk rombongan keluarga maupun sewa mancing seharian.',
            ],
        ];

        foreach ($boats as $boat) {
            Boat::query()->updateOrCreate(
                ['slug' => str($boat['name'])->slug()->toString()],
                [...$boat, 'trip_price' => 350000, 'is_active' => true]
            );
        }

        // Paket makan dari Kedai Bintang. Harga per paket; jumlah paket ditentukan sendiri oleh tamu.
        $meals = [
            ['code' => 'A', 'name' => 'Paket Udang', 'price' => 110000, 'menu' => ['Kelapa muda', 'Sup jagung', 'Ikan bakar', 'Udang goreng tepung', 'Nasi putih', 'Cah kangkung / pelecing', 'Tahu tempe', 'Buah segar']],
            ['code' => 'B', 'name' => 'Paket Cumi', 'price' => 95000, 'menu' => ['Kelapa muda', 'Sup jagung', 'Ikan bakar', 'Cumi goreng tepung', 'Nasi putih', 'Cah kangkung / pelecing', 'Tahu tempe', 'Buah segar']],
            ['code' => 'C', 'name' => 'Paket Ikan Bakar', 'price' => 85000, 'menu' => ['Kelapa muda', 'Sup jagung', 'Ikan bakar', 'Nasi putih', 'Cah kangkung / pelecing', 'Tahu tempe', 'Buah segar']],
            ['code' => 'D', 'name' => 'Paket Hemat', 'price' => 75000, 'menu' => ['Air mineral', 'Sup jagung', 'Ikan bakar', 'Nasi putih', 'Cah kangkung / pelecing', 'Tahu tempe', 'Buah segar']],
        ];

        foreach ($meals as $meal) {
            MealPackage::query()->updateOrCreate(['code' => $meal['code']], [...$meal, 'is_active' => true]);
        }

        $rentals = [
            ['name' => 'Pelampung', 'description' => 'Rompi pelampung untuk berenang dan snorkeling', 'price' => 15000],
            ['name' => 'Kacamata snorkeling', 'description' => 'Masker selam lengkap dengan selang snorkel', 'price' => 15000],
            ['name' => 'Fins (sepatu katak)', 'description' => 'Kaki katak agar berenang lebih ringan', 'price' => 15000],
        ];

        foreach ($rentals as $i => $item) {
            RentalItem::query()->updateOrCreate(['name' => $item['name']], [...$item, 'sort' => $i + 1, 'is_active' => true]);
        }
    }
}
