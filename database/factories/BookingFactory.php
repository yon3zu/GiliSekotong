<?php

namespace Database\Factories;

use App\Models\Boat;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Booking> */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => Booking::generateCode(),
            'user_id' => User::factory(),
            'boat_id' => fn () => Boat::query()->value('id'),
            'trip_date' => today()->addDays(7),
            'departure_time' => '08:30',
            'passengers' => 4,
            'contact_name' => fake()->name(),
            'contact_phone' => '081234567890',
            'trip_price' => 350000,
            'trip_type' => 'gili',
            'meal_total' => 0,
            'rental_total' => 0,
            'total' => 350000,
            'status' => Booking::STATUS_UNPAID,
        ];
    }
}
