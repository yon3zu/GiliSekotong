<?php

namespace App\Services;

use App\Models\Boat;
use App\Models\MealPackage;
use App\Models\RentalItem;

/**
 * Hitung rincian biaya pemesanan.
 * Harga sewa perahu per grup (per perahu), bukan per orang — sama untuk trip 3 gili maupun mancing.
 * Paket makan dihitung per paket, sewa alat per pcs.
 */
class BookingQuote
{
    public static function make(Boat $boat, int $passengers, array $mealQty, array $rentalQty): array
    {
        $meals = self::lines(
            MealPackage::query()->where('is_active', true)->whereIn('id', array_keys(array_filter($mealQty)))->orderBy('code')->get(),
            $mealQty
        );
        $rentals = self::lines(
            RentalItem::query()->where('is_active', true)->whereIn('id', array_keys(array_filter($rentalQty)))->orderBy('sort')->get(),
            $rentalQty
        );

        $mealTotal = array_sum(array_column($meals, 'subtotal'));
        $rentalTotal = array_sum(array_column($rentals, 'subtotal'));

        return [
            'boat' => $boat,
            'passengers' => $passengers,
            'trip_price' => $boat->trip_price,
            'meals' => $meals,
            'meal_total' => $mealTotal,
            'rentals' => $rentals,
            'rental_total' => $rentalTotal,
            'total' => $boat->trip_price + $mealTotal + $rentalTotal,
        ];
    }

    private static function lines($models, array $qty): array
    {
        return $models->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'qty' => (int) $qty[$m->id],
            'price' => $m->price,
            'subtotal' => $m->price * (int) $qty[$m->id],
        ])->values()->all();
    }
}
