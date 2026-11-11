<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Models\Booking;
use App\Models\MealPackage;
use App\Models\RentalItem;
use App\Models\SeaCondition;
use App\Models\User;
use App\Support\Sailing;
use Database\Seeders\MasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterSeeder::class);
    }

    private function draft(Boat $boat, array $override = []): array
    {
        return [
            'boat_id' => $boat->id,
            'trip_type' => 'gili',
            'trip_date' => today()->addDays(3)->toDateString(),
            'departure_time' => '08:30',
            'passengers' => 9,
            'meals' => [MealPackage::where('code', 'B')->value('id') => 3],
            'rentals' => [RentalItem::where('name', 'Kacamata snorkeling')->value('id') => 5, RentalItem::where('name', 'Pelampung')->value('id') => 2],
            ...$override,
        ];
    }

    public function test_guest_draft_survives_registration_and_becomes_booking(): void
    {
        $boat = Boat::where('capacity', '>=', 9)->first();

        $this->post(route('booking.draft'), $this->draft($boat))->assertRedirect(route('booking.checkout'));
        $this->get(route('booking.checkout'))->assertOk()->assertSee('Satu langkah lagi');

        $this->post(route('register'), [
            'name' => 'Budi', 'email' => 'budi@contoh.id', 'phone' => '081234567890',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('booking.checkout'));

        $this->assertNotNull(session('booking_draft'), 'Draf pemesanan hilang setelah registrasi');
        $this->get(route('booking.checkout'))->assertOk()->assertSee('Data kontak rombongan');

        $this->post(route('booking.confirm'), [
            'contact_name' => 'Budi', 'contact_phone' => '081234567890', 'agree' => '1',
        ])->assertRedirect();

        $booking = Booking::sole();
        // 350.000 sewa perahu + 3 × 95.000 paket B + 7 alat × 15.000
        $this->assertSame(350000 + 285000 + 105000, $booking->total);
        $this->assertCount(2, $booking->rentals);
        $this->assertSame(9, $booking->passengers);
        $this->assertCount(1, $booking->meals);
        $this->assertNull(session('booking_draft'));
    }

    public function test_trip_price_is_per_group_not_per_person(): void
    {
        $boat = Boat::where('capacity', '>=', 11)->first();
        $user = User::factory()->create(['role' => 'pemesan']);

        foreach ([4, 11] as $i => $pax) {
            $this->actingAs($user)->post(route('booking.draft'), $this->draft($boat, [
                'passengers' => $pax, 'meals' => [], 'rentals' => [], 'trip_type' => $i ? 'mancing' : 'gili', 'trip_date' => today()->addDays(5 + $i)->toDateString(),
            ]));
            $this->post(route('booking.confirm'), ['contact_name' => 'X', 'contact_phone' => '0812345678', 'agree' => '1']);
        }

        $this->assertSame([350000, 350000], Booking::orderBy('id')->pluck('total')->all());
        $this->assertSame(['gili', 'mancing'], Booking::orderBy('id')->pluck('trip_type')->all());
    }

    public function test_cannot_double_book_boat_or_exceed_capacity(): void
    {
        $boat = Boat::orderBy('capacity')->first();
        $user = User::factory()->create();
        Booking::factory()->for($user)->for($boat)->create(['trip_date' => today()->addDays(3)]);

        $this->actingAs($user)->post(route('booking.draft'), $this->draft($boat, ['passengers' => 2, 'rentals' => []]))
            ->assertSessionHasErrors('trip_date');

        $this->post(route('booking.draft'), $this->draft($boat, ['passengers' => $boat->capacity + 1, 'trip_date' => today()->addDays(4)->toDateString()]))
            ->assertSessionHasErrors('passengers');
    }

    public function test_rentals_capped_by_passengers_and_boats_capped_at_twelve(): void
    {
        $boat = Boat::where('capacity', '>=', 9)->first();

        $this->post(route('booking.draft'), $this->draft($boat, ['passengers' => 4, 'rentals' => [RentalItem::value('id') => 5]]))
            ->assertSessionHasErrors('rentals.*');

        $this->assertSame(12, Boat::max('capacity'));
    }

    public function test_date_marked_not_safe_blocks_booking(): void
    {
        $boat = Boat::where('capacity', '>=', 9)->first();
        SeaCondition::create([
            'date' => today()->addDays(3), 'cuaca' => 'Hujan Lebat', 'wind_knot' => 25, 'wave_m' => 2.8,
            'jarak_pandang' => 'Buruk', 'pasang_surut' => 'Surut', 'angin' => 'Kencang', 'gelombang' => 'Tinggi',
            'prediction' => Sailing::TIDAK_LAYAK,
        ]);

        $this->post(route('booking.draft'), $this->draft($boat))->assertSessionHasErrors('trip_date');
    }

    public function test_role_access(): void
    {
        $pemesan = User::factory()->create(['role' => 'pemesan']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($pemesan)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.boats.index'))->assertForbidden();
    }

    public function test_other_users_cannot_see_my_booking(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs(User::factory()->create())->get(route('orders.show', $booking))->assertNotFound();
        $this->actingAs($booking->user)->get(route('orders.show', $booking))->assertOk();
    }
}
