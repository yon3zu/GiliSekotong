<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Models\Booking;
use App\Models\Review;
use App\Models\SeaCondition;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\MasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewAndCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterSeeder::class);
    }

    private function booking(array $attrs = []): Booking
    {
        return Booking::factory()->create(['status' => Booking::STATUS_CONFIRMED, 'trip_date' => today()->subDay(), ...$attrs]);
    }

    public function test_customer_completes_after_departure_but_not_before(): void
    {
        $future = $this->booking(['trip_date' => today()->addDays(3)]);
        $this->actingAs($future->user)->post(route('orders.complete', $future))->assertForbidden();
        $this->assertSame(Booking::STATUS_CONFIRMED, $future->fresh()->status);

        $past = $this->booking();
        $this->actingAs($past->user)->post(route('orders.complete', $past))->assertRedirect();
        $this->assertSame(Booking::STATUS_DONE, $past->fresh()->status);
        $this->assertNotNull($past->fresh()->completed_at);
        $this->assertNull($past->fresh()->checked_in_at);
    }

    public function test_other_customer_cannot_complete_or_review(): void
    {
        $booking = $this->booking(['status' => Booking::STATUS_DONE]);
        $other = User::factory()->create();

        $this->actingAs($other)->post(route('orders.complete', $booking))->assertNotFound();
        $this->actingAs($other)->post(route('orders.review', $booking), ['rating' => 1])->assertNotFound();
        $this->assertSame(0, Review::count());
    }

    public function test_auto_complete_respects_delay_and_unsafe_sea(): void
    {
        Setting::put(['auto_complete_days' => '2']);
        $tooRecent = $this->booking(['trip_date' => today()->subDay()]);
        $old = $this->booking(['trip_date' => today()->subDays(2)]);
        $unpaid = $this->booking(['trip_date' => today()->subDays(5), 'status' => Booking::STATUS_UNPAID]);
        $unsafe = $this->booking(['trip_date' => today()->subDays(3)]);
        SeaCondition::create([
            'date' => today()->subDays(3)->toDateString(), 'cuaca' => 'Badai', 'angin' => 'Kencang', 'gelombang' => 'Tinggi',
            'jarak_pandang' => 'Buruk', 'pasang_surut' => 'Pasang', 'wind_knot' => 30, 'wave_m' => 3, 'prediction' => 'Tidak Layak',
        ]);

        $this->artisan('bookings:complete')->assertSuccessful();

        $this->assertSame(Booking::STATUS_CONFIRMED, $tooRecent->fresh()->status);
        $this->assertSame(Booking::STATUS_DONE, $old->fresh()->status);
        $this->assertSame(Booking::STATUS_UNPAID, $unpaid->fresh()->status);
        $this->assertSame(Booking::STATUS_CONFIRMED, $unsafe->fresh()->status);
    }

    public function test_review_needs_finished_booking_and_is_one_per_booking(): void
    {
        $booking = $this->booking();
        $this->actingAs($booking->user)->post(route('orders.review', $booking), ['rating' => 5])->assertForbidden();

        $booking->markCompleted();
        $this->actingAs($booking->user)->post(route('orders.review', $booking), ['rating' => 9])->assertSessionHasErrors('rating');
        $this->actingAs($booking->user)->post(route('orders.review', $booking), ['rating' => 4, 'comment' => 'Nakhodanya ramah'])->assertRedirect();
        $this->actingAs($booking->user)->post(route('orders.review', $booking), ['rating' => 5, 'comment' => 'Nakhodanya ramah sekali'])->assertRedirect();

        $review = Review::sole();
        $this->assertSame(5, $review->rating);
        $this->assertSame($booking->boat_id, $review->boat_id);
    }

    public function test_visible_reviews_show_publicly_and_admin_can_hide(): void
    {
        $user = User::factory()->create(['name' => 'Rina Pemesan']);
        $booking = $this->booking(['status' => Booking::STATUS_DONE, 'user_id' => $user->id]);
        $review = $booking->review()->create(['user_id' => $user->id, 'boat_id' => $booking->boat_id, 'rating' => 5, 'comment' => 'Airnya jernih sekali']);
        $boat = Boat::find($booking->boat_id);

        $this->get(route('boats.show', $boat))->assertOk()
            ->assertSee('Airnya jernih sekali')->assertSee('Rina P.')->assertDontSee('Rina Pemesan')
            ->assertSee('aggregateRating', false);
        $this->get(route('home'))->assertOk()->assertSee('Airnya jernih sekali');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.reviews.index'))->assertOk()->assertSee('Airnya jernih sekali');
        $this->actingAs($admin)->post(route('admin.reviews.toggle', $review))->assertRedirect();

        $this->get(route('boats.show', $boat))->assertDontSee('Airnya jernih sekali')->assertDontSee('aggregateRating', false);
        $this->get(route('home'))->assertDontSee('Airnya jernih sekali');
    }

    public function test_order_pages_offer_completion_then_review(): void
    {
        $booking = $this->booking();
        $this->actingAs($booking->user)->get(route('orders.show', $booking))->assertSee('Selesaikan pesanan')->assertDontSee('Kirim ulasan');
        $this->actingAs($booking->user)->get(route('orders.index'))->assertSee('Tandai selesai');

        $booking->markCompleted();
        $this->actingAs($booking->user)->get(route('orders.show', $booking))->assertDontSee('Selesaikan pesanan')->assertSee('Kirim ulasan');
        $this->actingAs($booking->user)->get(route('orders.index'))->assertSee('Beri ulasan');
    }
}
