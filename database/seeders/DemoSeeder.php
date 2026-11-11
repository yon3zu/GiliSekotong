<?php

namespace Database\Seeders;

use App\Models\Boat;
use App\Models\Booking;
use App\Models\MealPackage;
use App\Models\RentalItem;
use App\Models\SeaCondition;
use App\Models\User;
use App\Services\BookingQuote;
use App\Services\SailingModelTrainer;
use App\Support\Sailing;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $trainer = app(SailingModelTrainer::class);
        $superadmin = User::query()->where('role', 'superadmin')->first();
        $trainer->train('C4.5', 0.2, 42, $superadmin?->id, 10);

        // Contoh catatan petugas untuk hari ke-3 s.d. ke-9. Hari ini s.d. lusa diisi otomatis
        // dari prakiraan BMKG lewat `php artisan bmkg:sync`.
        $forecast = [
            ['Cerah', 6, 0.3, 'Baik', 'Normal'],
            ['Cerah', 8, 0.4, 'Baik', 'Pasang'],
            ['Berawan', 12, 0.7, 'Baik', 'Normal'],
            ['Hujan Ringan', 15, 0.9, 'Sedang', 'Surut'],
            ['Hujan Lebat', 24, 1.8, 'Buruk', 'Surut'],
            ['Berawan', 14, 1.4, 'Sedang', 'Normal'],
            ['Cerah', 9, 0.5, 'Baik', 'Pasang'],
            ['Cerah', 7, 0.3, 'Baik', 'Normal'],
            ['Berawan', 10, 0.6, 'Baik', 'Surut'],
            ['Cerah', 5, 0.2, 'Baik', 'Pasang'],
        ];

        foreach ($forecast as $i => [$cuaca, $wind, $wave, $visibility, $tide]) {
            if ($i < 3) {
                continue;
            }
            $condition = SeaCondition::forDate(today()->addDays($i)->toDateString());
            $condition->fill([
                'cuaca' => $cuaca, 'wind_knot' => $wind, 'wave_m' => $wave,
                'jarak_pandang' => $visibility, 'pasang_surut' => $tide,
                'angin' => Sailing::windCategory($wind), 'gelombang' => Sailing::waveCategory($wave),
                'updated_by' => $superadmin?->id,
            ]);
            $trainer->applyPrediction($condition)->save();
        }

        if (Booking::query()->exists()) {
            return;
        }

        $customer = User::query()->where('role', 'pemesan')->first();
        $boats = Boat::all()->keyBy('slug');
        $mealB = MealPackage::query()->where('code', 'B')->first();
        [$jacket, $mask, $fins] = RentalItem::query()->orderBy('sort')->pluck('id')->all();

        $demo = [
            ['sinar-nanggu', 'gili', 2, 8, [$mealB->id => 3], [$jacket => 2, $mask => 4, $fins => 4], Booking::STATUS_CONFIRMED],
            ['bintang-tawun', 'mancing', 3, 4, [], [], Booking::STATUS_REVIEW],
            ['lumba-kedis', 'gili', -10, 12, [$mealB->id => 6], [$mask => 8], Booking::STATUS_DONE],
        ];

        foreach ($demo as [$slug, $type, $offset, $pax, $meals, $rentals, $status]) {
            $boat = $boats[$slug];
            $quote = BookingQuote::make($boat, $pax, $meals, $rentals);
            $booking = Booking::create([
                'code' => Booking::generateCode(),
                'user_id' => $customer->id,
                'boat_id' => $boat->id,
                'trip_type' => $type,
                'trip_date' => today()->addDays($offset),
                'departure_time' => '08:30',
                'passengers' => $pax,
                'contact_name' => $customer->name,
                'contact_phone' => $customer->phone,
                'trip_price' => $quote['trip_price'],
                'meal_total' => $quote['meal_total'],
                'rental_total' => $quote['rental_total'],
                'total' => $quote['total'],
                'status' => $status,
                'paid_at' => $status !== Booking::STATUS_UNPAID ? now() : null,
                'verified_at' => in_array($status, [Booking::STATUS_CONFIRMED, Booking::STATUS_DONE]) ? now() : null,
                'verified_by' => in_array($status, [Booking::STATUS_CONFIRMED, Booking::STATUS_DONE]) ? $superadmin->id : null,
                'checked_in_at' => $status === Booking::STATUS_DONE ? today()->addDays($offset)->setTime(8, 15) : null,
            ]);
            $booking->saveLines($quote);
        }
    }
}
