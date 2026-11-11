<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Models\Booking;
use App\Models\BoatBlock;
use App\Models\User;
use Database\Seeders\MasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoatOwnerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterSeeder::class);
    }

    private function owner(array $boats): User
    {
        $owner = User::factory()->create(['role' => 'pemilik', 'phone' => '081300000009', 'email' => null, 'password' => 'kapal123']);
        Boat::whereIn('id', collect($boats)->pluck('id'))->update(['owner_id' => $owner->id]);

        return $owner;
    }

    public function test_admin_registers_owner_who_logs_in_with_phone(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $boat = Boat::first();

        $this->actingAs($admin)->post(route('admin.owners.store'), [
            'name' => 'Pak Amir', 'phone' => '+62 813-1111-2222', 'password' => 'kapal123', 'boats' => [$boat->id], 'is_active' => '1',
        ])->assertRedirect(route('admin.owners.index'));

        $owner = User::where('role', 'pemilik')->sole();
        $this->assertSame('081311112222', $owner->phone);
        $this->assertNull($owner->email);
        $this->assertTrue($boat->fresh()->owner->is($owner));

        $this->post(route('logout'));
        $this->post(route('login'), ['login' => '0813 1111 2222', 'password' => 'kapal123'])->assertRedirect(route('owner.home'));
        $this->get(route('owner.home'))->assertOk()->assertSee($boat->name);
    }

    public function test_offline_report_closes_date_for_online_booking(): void
    {
        $boat = Boat::first();
        $owner = $this->owner([$boat]);
        $date = today()->addDays(2)->toDateString();

        $this->actingAs($owner)->post(route('owner.report.store'), [
            'boat_id' => $boat->id, 'date' => $date, 'reason' => 'tamu_offline', 'passengers' => 7, 'amount' => 350000,
        ])->assertRedirect(route('owner.home'));

        $this->assertTrue($boat->isBookedOn($date));
        $this->getJson(route('boats.availability', [$boat, 'date' => $date]))->assertJson(['booked' => true]);

        $this->actingAs(User::factory()->create())->post(route('booking.draft'), [
            'boat_id' => $boat->id, 'trip_type' => 'gili', 'trip_date' => $date, 'departure_time' => '08:30', 'passengers' => 4,
        ])->assertSessionHasErrors('trip_date');

        // Pembukuan pemilik ikut mencatat tamu offline.
        $this->actingAs($owner)->get(route('owner.books'))->assertOk()->assertSee('Rp350.000');

        // Batalkan → bisa dipesan lagi.
        $this->post(route('owner.report.cancel', BoatBlock::sole()));
        $this->assertFalse($boat->fresh()->isBookedOn($date));
    }

    public function test_owner_cannot_close_date_that_already_has_online_booking(): void
    {
        $boat = Boat::first();
        $owner = $this->owner([$boat]);
        $booking = Booking::factory()->for($boat)->create(['trip_date' => today()->addDays(3), 'status' => Booking::STATUS_CONFIRMED]);

        $this->actingAs($owner)->post(route('owner.report.store'), [
            'boat_id' => $boat->id, 'date' => $booking->trip_date->toDateString(), 'reason' => 'tamu_offline',
        ])->assertSessionHasErrors('date');

        $this->assertSame(0, BoatBlock::count());
        $this->get(route('owner.history'))->assertOk()->assertSee($booking->contact_name);
    }

    public function test_owners_only_see_their_own_boats(): void
    {
        [$mine, $theirs] = Boat::orderBy('id')->take(2)->get();
        $owner = $this->owner([$mine]);
        $other = User::factory()->create(['role' => 'pemilik', 'phone' => '081300000010']);
        $theirs->update(['owner_id' => $other->id]);
        $block = BoatBlock::create(['boat_id' => $theirs->id, 'date' => today()->addDay(), 'reason' => 'tamu_offline']);

        $this->actingAs($owner)->post(route('owner.report.store'), ['boat_id' => $theirs->id, 'date' => today()->toDateString(), 'reason' => 'tamu_offline'])
            ->assertSessionHasErrors('boat_id');
        $this->post(route('owner.report.cancel', $block))->assertNotFound();
        $this->get(route('owner.home'))->assertSee($mine->name)->assertDontSee($theirs->name);
    }

    public function test_role_boundaries(): void
    {
        $owner = $this->owner([Boat::first()]);

        $this->actingAs($owner)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'pemesan']))->get(route('owner.home'))->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.blocks.index'))->assertOk();

        // Pendaftaran publik selalu menjadi pemesan, tidak bisa memilih peran pemilik.
        auth()->logout();
        $this->post(route('register'), [
            'name' => 'X', 'email' => 'x@contoh.id', 'phone' => '081299999999', 'role' => 'pemilik',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ]);
        $this->assertSame('pemesan', User::where('email', 'x@contoh.id')->value('role'));
    }
}
