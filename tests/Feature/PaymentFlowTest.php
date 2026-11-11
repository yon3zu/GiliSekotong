<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Database\Seeders\MasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_proof_verify_and_ticket(): void
    {
        Storage::fake('local');
        $this->seed(MasterSeeder::class);
        $booking = Booking::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($booking->user)->get(route('orders.ticket', $booking))->assertForbidden();

        $this->post(route('orders.proof', $booking), ['proof' => UploadedFile::fake()->image('bukti.jpg')])->assertRedirect();
        $this->assertSame(Booking::STATUS_REVIEW, $booking->fresh()->status);
        Storage::disk('local')->assertExists($booking->fresh()->payment_proof);

        $this->actingAs($admin)->post(route('admin.bookings.status', $booking), ['action' => 'verify'])->assertRedirect();
        $this->assertSame(Booking::STATUS_CONFIRMED, $booking->fresh()->status);

        $this->actingAs($booking->user)->get(route('orders.ticket', $booking))
            ->assertOk()->assertSee($booking->code)->assertSee('<svg', false);

        $this->actingAs($admin)->get(route('admin.checkin', ['kode' => $booking->code]))->assertOk()->assertSee('Konfirmasi check-in');
        $this->post(route('admin.bookings.status', $booking), ['action' => 'checkin']);
        $this->assertNotNull($booking->fresh()->checked_in_at);
    }

    public function test_admin_pages_render(): void
    {
        $this->seed();
        $super = User::where('role', 'superadmin')->first();
        $booking = Booking::first();

        foreach ([
            'admin.dashboard', 'admin.bookings.index', 'admin.sea.index', 'admin.bmkg.index', 'admin.tree.index', 'admin.records.index',
            'admin.reports.index', 'admin.boats.index', 'admin.boats.create', 'admin.seo.edit', 'admin.email.edit', 'admin.meals.index', 'admin.owners.index', 'admin.owners.create', 'admin.blocks.index', 'admin.rentals.index', 'admin.rentals.create', 'admin.users.index', 'admin.settings.edit',
        ] as $route) {
            $this->actingAs($super)->get(route($route))->assertOk();
        }
        $this->get(route('admin.bookings.show', $booking))->assertOk();
        $this->get(route('admin.records.export'))->assertOk();
    }
}
