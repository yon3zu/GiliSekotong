<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Database\Seeders\MasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferUniqueCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterSeeder::class);
    }

    public function test_open_bookings_with_same_total_get_different_amounts(): void
    {
        $amounts = Booking::factory()->count(50)->create()->map->transferAmount();

        $this->assertSame(50, $amounts->unique()->count());
        $amounts->each(fn ($a) => $this->assertTrue($a > 350000 && $a <= 350999));
    }

    public function test_code_does_not_change_price_and_is_shown_to_customer(): void
    {
        $booking = Booking::factory()->create(['unique_code' => 0]);
        $booking->update(['unique_code' => 67]);

        $this->assertSame(350000, $booking->total);
        $this->assertSame(350067, $booking->transferAmount());
        $this->actingAs($booking->user)->get(route('orders.show', $booking))
            ->assertSee('Rp350.067', false)->assertSee('067');
    }

    public function test_admin_finds_booking_by_amount_from_bank_statement(): void
    {
        $target = Booking::factory()->create(['contact_name' => 'Tamu Target']);
        $target->update(['unique_code' => 67]);
        $other = Booking::factory()->create(['contact_name' => 'Tamu Lain']);
        $other->update(['unique_code' => 68]);
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['350.067', '350067', 'Rp'] as $i => $term) {
            $response = $this->actingAs($admin)->get(route('admin.bookings.index', ['q' => $term]));
            $i < 2 ? $response->assertSee('Tamu Target')->assertDontSee('Tamu Lain') : $response->assertDontSee('Tamu Target');
        }
    }

    public function test_admin_confirms_money_received_without_proof(): void
    {
        $booking = Booking::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.bookings.show', $booking))
            ->assertSee('Dana sudah masuk')->assertSee(number_format($booking->transferAmount(), 0, ',', '.'));
        $this->actingAs($admin)->post(route('admin.bookings.status', $booking), ['action' => 'verify'])->assertRedirect();

        $fresh = $booking->fresh();
        $this->assertSame(Booking::STATUS_CONFIRMED, $fresh->status);
        $this->assertSame('transfer', $fresh->payment_method);
        $this->assertNotNull($fresh->paid_at);
        $this->assertSame($admin->id, $fresh->verified_by);
    }

    public function test_customer_cannot_confirm_own_payment(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs($booking->user)->post(route('admin.bookings.status', $booking), ['action' => 'verify'])->assertForbidden();
        $this->assertSame(Booking::STATUS_UNPAID, $booking->fresh()->status);
    }
}
