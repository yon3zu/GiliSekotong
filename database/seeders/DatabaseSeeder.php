<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MasterSeeder::class,
            OwnerSeeder::class,
            SailingRecordSeeder::class,
            DemoSeeder::class,
            DeveloperSeeder::class,
        ]);
    }
}
