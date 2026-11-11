<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmedMail;
use App\Mail\BookingCreatedMail;
use App\Mail\PaymentProofMail;
use App\Mail\TestMail;
use App\Models\Boat;
use App\Models\Booking;
use App\Models\MealPackage;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\MasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoAndMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterSeeder::class);
        $this->withoutDefer();
    }

    public function test_public_pages_have_seo_tags_and_structured_data(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('<title>Sewa Perahu Trip 3 Gili Sekotong, Lombok | GiliSekotong</title>', false)
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false)
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false)
            ->assertSee('og-gilisekotong.png', false)
            ->assertSee('"@type":"TravelAgency"', false)
            ->assertSee('"@type":"WebSite"', false);

        $boat = Boat::first();
        $this->get(route('boats.show', $boat))->assertOk()
            ->assertSee("<title>{$boat->name}: Sewa Perahu {$boat->capacity} Orang ke 3 Gili Sekotong | GiliSekotong</title>", false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"price":350000', false)
            ->assertSee('"@type":"BreadcrumbList"', false);

        $this->get(route('contact'))->assertSee('"@type":"FAQPage"', false);
        $this->get(route('destinations'))->assertSee('"@type":"TouristAttraction"', false);

        // Halaman pribadi tidak diindeks.
        $this->get(route('login'))->assertSee('content="noindex, nofollow"', false);
    }

    public function test_sitemap_and_robots(): void
    {
        $boat = Boat::first();
        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('boats.show', $boat), false)
            ->assertSee(route('sea'), false);

        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('Sitemap: '.route('sitemap'));

        Setting::put(['seo_indexing' => '0']);
        $this->get('/robots.txt')->assertSee("Disallow: /\n", false)->assertDontSee('Sitemap:');
        $this->get(route('home'))->assertSee('content="noindex, nofollow"', false);
    }

    public function test_admin_updates_seo_settings(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $payload = [
            'seo_site_name' => 'GiliSekotong', 'seo_indexing' => '1', 'seo_ga4_id' => 'G-ABC1234',
            'seo_google_verification' => 'abcDEF_123', 'biz_type' => 'TravelAgency', 'biz_postal_code' => '83365',
        ];
        foreach (\App\Support\Seo::PAGES as $p) {
            $payload["seo_page_{$p['key']}_title"] = Setting::get("seo_page_{$p['key']}_title");
            $payload["seo_page_{$p['key']}_description"] = Setting::get("seo_page_{$p['key']}_description");
        }
        $payload['seo_page_home_title'] = 'Trip Gili Nanggu Sudak Kedis dari Sekotong | GiliSekotong';

        $this->actingAs($admin)->get(route('admin.seo.edit'))->assertOk()->assertSee('Daftar periksa');
        $this->put(route('admin.seo.update'), $payload)->assertSessionHasNoErrors();

        $this->get(route('home'))
            ->assertSee('<title>Trip Gili Nanggu Sudak Kedis dari Sekotong | GiliSekotong</title>', false)
            ->assertSee('<meta name="google-site-verification" content="abcDEF_123">', false)
            ->assertSee('googletagmanager.com/gtag/js?id=G-ABC1234', false);

        $this->put(route('admin.seo.update'), [...$payload, 'seo_ga4_id' => 'UA-123'])->assertSessionHasErrors('seo_ga4_id');
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.seo.edit'))->assertForbidden();
    }

    public function test_smtp_password_is_encrypted_and_test_email_is_sent(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'superadmin']);
        $smtp = [
            'mail_host' => 'smtp.example.test', 'mail_port' => 587, 'mail_encryption' => 'tls', 'mail_username' => 'halo@gilisekotong.com',
            'mail_password' => 'rahasia-smtp', 'mail_from_address' => 'halo@gilisekotong.com', 'mail_from_name' => 'GiliSekotong', 'mail_enabled' => '1',
        ];

        $this->actingAs($admin)->put(route('admin.email.update'), $smtp)->assertSessionHasNoErrors();
        $raw = DB::table('settings')->where('key', 'mail_password')->value('value');
        $this->assertNotSame('rahasia-smtp', $raw);
        $this->assertSame('rahasia-smtp', Setting::get('mail_password'));

        // Mengosongkan password saat menyimpan tidak menghapus password lama.
        $this->put(route('admin.email.update'), [...$smtp, 'mail_password' => ''])->assertSessionHasNoErrors();
        $this->assertSame('rahasia-smtp', Setting::get('mail_password'));
        $this->get(route('admin.email.edit'))->assertOk()->assertDontSee('rahasia-smtp');

        $this->post(route('admin.email.test'), ['test_to' => 'uji@contoh.id'])->assertSessionHasNoErrors();
        Mail::assertSent(TestMail::class, fn ($m) => $m->hasTo('uji@contoh.id'));
    }

    public function test_booking_flow_sends_notifications_when_enabled(): void
    {
        Mail::fake();
        Storage::fake('local');
        Setting::put(['mail_enabled' => '1', 'mail_host' => 'smtp.example.test', 'mail_from_address' => 'halo@gilisekotong.com', 'mail_admin_address' => 'admin@gilisekotong.com']);

        $user = User::factory()->create(['role' => 'pemesan', 'email' => 'tamu@contoh.id']);
        $boat = Boat::first();
        $this->actingAs($user)->post(route('booking.draft'), [
            'boat_id' => $boat->id, 'trip_type' => 'gili', 'trip_date' => today()->addDays(4)->toDateString(),
            'departure_time' => '08:30', 'passengers' => 5, 'meals' => [MealPackage::first()->id => 1],
        ]);
        $this->post(route('booking.confirm'), ['contact_name' => 'Tamu', 'contact_phone' => '081234567890', 'agree' => '1']);
        Mail::assertSent(BookingCreatedMail::class, fn ($m) => $m->hasTo('tamu@contoh.id'));

        $booking = Booking::sole();
        $this->post(route('orders.proof', $booking), ['proof' => UploadedFile::fake()->image('bukti.jpg')]);
        Mail::assertSent(PaymentProofMail::class, fn ($m) => $m->hasTo('admin@gilisekotong.com'));

        $this->actingAs(User::factory()->create(['role' => 'admin']))->post(route('admin.bookings.status', $booking), ['action' => 'verify']);
        Mail::assertSent(BookingConfirmedMail::class, fn ($m) => $m->hasTo('tamu@contoh.id'));
    }

    public function test_no_email_when_notifications_disabled(): void
    {
        Mail::fake();
        Setting::put(['mail_enabled' => '0', 'mail_host' => 'smtp.example.test']);
        $booking = Booking::factory()->create(['status' => Booking::STATUS_REVIEW]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))->post(route('admin.bookings.status', $booking), ['action' => 'verify']);
        Mail::assertNothingSent();
    }
}
