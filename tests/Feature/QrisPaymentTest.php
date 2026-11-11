<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\MasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QrisPaymentTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://qrcepat.com/api/v1/transactions*';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterSeeder::class);
        Setting::put(['qris_enabled' => '1', 'qris_api_key' => 'qc_kunciuji']);
        Http::preventStrayRequests();
    }

    private function transaction(string $status = 'pending', array $attrs = []): array
    {
        return ['order_id' => 'QRIS-1', 'status' => $status, 'amount' => 350000, 'fee_amount' => 5950, 'merchant_amount' => 344050,
            'fee_payer' => 'merchant', 'qr_url' => 'https://api.midtrans.com/v2/qris/abc/qr-code', 'paid_at' => null, ...$attrs];
    }

    private function pending(array $attrs = []): Booking
    {
        return Booking::factory()->create(['qris_order_id' => 'QRIS-1', 'qris_status' => 'pending', 'qris_url' => 'https://api.midtrans.com/qr',
            'qris_amount' => 350000, 'qris_created_at' => now(), ...$attrs]);
    }

    public function test_api_key_is_stored_encrypted(): void
    {
        $raw = DB::table('settings')->where('key', 'qris_api_key')->value('value');
        $this->assertNotSame('qc_kunciuji', $raw);
        $this->assertSame('qc_kunciuji', Crypt::decryptString($raw));
    }

    public function test_customer_gets_qr_with_booking_total(): void
    {
        Http::fake([self::URL => Http::response($this->transaction(), 201)]);
        $booking = Booking::factory()->create();

        $this->actingAs($booking->user)->post(route('orders.qris', $booking))->assertRedirect()->assertSessionHasNoErrors();

        Http::assertSent(fn ($r) => $r->method() === 'POST' && $r['amount'] === 350000 && $r->hasHeader('Authorization', 'Bearer qc_kunciuji'));
        $this->assertSame('QRIS-1', $booking->fresh()->qris_order_id);
        $this->actingAs($booking->user)->get(route('orders.show', $booking))->assertSee('https://api.midtrans.com/v2/qris/abc/qr-code', false);
    }

    public function test_pending_qr_is_reused_and_expired_one_replaced(): void
    {
        Http::fake([self::URL => Http::response($this->transaction(attrs: ['order_id' => 'QRIS-2']), 201)]);
        $booking = $this->pending();

        $this->actingAs($booking->user)->post(route('orders.qris', $booking));
        Http::assertNothingSent();

        $booking->update(['qris_status' => 'expire']);
        $this->actingAs($booking->user)->post(route('orders.qris', $booking));
        $this->assertSame('QRIS-2', $booking->fresh()->qris_order_id);
        $this->assertSame('pending', $booking->fresh()->qris_status);
    }

    public function test_settlement_confirms_booking_and_is_not_checked_again(): void
    {
        Http::fake([self::URL => Http::response($this->transaction('settlement'))]);
        $booking = $this->pending();

        $this->actingAs($booking->user)->getJson(route('orders.qris.status', $booking))->assertOk()
            ->assertJson(['status' => 'settlement', 'booking' => Booking::STATUS_CONFIRMED]);

        $fresh = $booking->fresh();
        $this->assertSame(Booking::STATUS_CONFIRMED, $fresh->status);
        $this->assertSame('qris', $fresh->payment_method);
        $this->assertNotNull($fresh->paid_at);
        $this->assertTrue($fresh->hasTicket());

        Cache::flush();
        $this->artisan('qris:sync')->assertSuccessful();
        Http::assertSentCount(1);
    }

    public function test_sync_command_confirms_when_page_closed_and_marks_expired(): void
    {
        Http::fake([
            'qrcepat.com/api/v1/transactions/QRIS-1' => Http::response($this->transaction('settlement')),
            'qrcepat.com/api/v1/transactions/QRIS-9' => Http::response($this->transaction('expire', ['order_id' => 'QRIS-9'])),
        ]);
        $paid = $this->pending();
        $gone = $this->pending(['qris_order_id' => 'QRIS-9']);

        $this->artisan('qris:sync')->assertSuccessful();

        $this->assertSame(Booking::STATUS_CONFIRMED, $paid->fresh()->status);
        $this->assertSame(Booking::STATUS_UNPAID, $gone->fresh()->status);
        $this->assertSame('expire', $gone->fresh()->qris_status);
    }

    public function test_payment_after_cancellation_flags_refund_instead_of_confirming(): void
    {
        Http::fake([self::URL => Http::response($this->transaction('settlement'))]);
        $booking = $this->pending(['status' => Booking::STATUS_CANCELLED]);

        $this->artisan('qris:sync')->assertSuccessful();

        $fresh = $booking->fresh();
        $this->assertSame(Booking::STATUS_CANCELLED, $fresh->status);
        $this->assertSame('settlement', $fresh->qris_status);
        $this->assertStringContainsString('kembalikan dana', $fresh->admin_note);
    }

    public function test_network_or_html_error_keeps_booking_pending(): void
    {
        Http::fake([self::URL => Http::response('<html>404 Not Found</html>', 404)]);
        $booking = $this->pending();

        $this->artisan('qris:sync')->assertSuccessful();

        $this->assertSame('pending', $booking->fresh()->qris_status);
        $this->assertSame(Booking::STATUS_UNPAID, $booking->fresh()->status);
    }

    public function test_api_errors_are_shown_without_leaking_details(): void
    {
        Http::fake([self::URL => Http::response(['message' => 'API key tidak valid.'], 401)]);
        $booking = Booking::factory()->create();

        $this->actingAs($booking->user)->post(route('orders.qris', $booking))
            ->assertSessionHasErrors(['qris' => 'Pembayaran QRIS sedang tidak tersedia. Silakan bayar lewat transfer bank.']);
        $this->assertNull($booking->fresh()->qris_order_id);
    }

    public function test_unsafe_qr_url_is_rejected(): void
    {
        Http::fake([self::URL => Http::response($this->transaction(attrs: ['qr_url' => 'javascript:alert(1)']), 201)]);
        $booking = Booking::factory()->create();

        $this->actingAs($booking->user)->post(route('orders.qris', $booking))->assertSessionHasErrors('qris');
        $this->assertNull($booking->fresh()->qris_order_id);
    }

    public function test_access_rules(): void
    {
        Http::fake();
        $booking = Booking::factory()->create();

        $this->actingAs(User::factory()->create())->post(route('orders.qris', $booking))->assertNotFound();
        $this->actingAs(User::factory()->create())->getJson(route('orders.qris.status', $booking))->assertNotFound();

        $booking->update(['status' => Booking::STATUS_CONFIRMED]);
        $this->actingAs($booking->user)->post(route('orders.qris', $booking))->assertForbidden();

        $booking->update(['status' => Booking::STATUS_UNPAID]);
        Setting::put(['qris_enabled' => '0']);
        $this->actingAs($booking->user)->post(route('orders.qris', $booking))->assertForbidden();
        $this->actingAs($booking->user)->get(route('orders.show', $booking))->assertDontSee('Tampilkan kode QRIS');
        Http::assertNothingSent();
    }

    public function test_only_superadmin_manages_settings_and_key_is_never_shown(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.payment.edit'))->assertForbidden();

        $root = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($root)->get(route('admin.payment.edit'))->assertOk()->assertDontSee('qc_kunciuji');

        // Kunci kosong tidak menimpa yang tersimpan.
        $this->actingAs($root)->put(route('admin.payment.update'), ['qris_enabled' => '1', 'qris_api_key' => ''])->assertRedirect();
        $this->assertSame('qc_kunciuji', Setting::get('qris_api_key'));

        Http::fake([self::URL => Http::response('<html></html>', 404)]);
        $this->actingAs($root)->post(route('admin.payment.test'))->assertSessionHasNoErrors();
    }

    public function test_key_test_reports_rejected_key(): void
    {
        Http::fake([self::URL => Http::response(['message' => 'API key tidak valid.'], 401)]);
        $root = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($root)->post(route('admin.payment.test'))->assertSessionHasErrors('qris_api_key');
    }
}
