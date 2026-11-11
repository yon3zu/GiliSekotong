<?php

namespace Tests\Feature;

use App\Models\BmkgAlert;
use App\Models\BmkgEarthquake;
use App\Models\BmkgForecast;
use App\Models\BmkgSync;
use App\Models\SeaCondition;
use App\Services\Bmkg\BmkgImporter;
use App\Services\SailingModelTrainer;
use App\Support\Sailing;
use Database\Seeders\SailingRecordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BmkgIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Respons fixture diambil 16 Okt 2026 malam; uji pada pagi berikutnya.
        Carbon::setTestNow(Carbon::create(2026, 10, 17, 6, 0, 0, 'Asia/Makassar'));
        $this->seed(SailingRecordSeeder::class);
        app(SailingModelTrainer::class)->train('C4.5', 0.2, 42, null, 10);
    }

    private function fixture(string $name): string
    {
        return file_get_contents(base_path("tests/Fixtures/bmkg/$name"));
    }

    private function fakeBmkg(bool $withLocalHazards = true): void
    {
        $rss = $this->fixture('nowcast.xml');
        $quakes = json_decode($this->fixture('gempadirasakan.json'), true);

        if ($withLocalHazards) {
            $ntb = '<item>
                <title>Hujan Lebat disertai Petir di Nusa Tenggara Barat</title>
                <link>https://www.bmkg.go.id/alerts/nowcast/id/CNB20261017001_alert.xml</link>
                <description>Hujan lebat disertai petir akan terjadi pada 17 October 2026, 09:00 WITA di sebagian wilayah Nusa Tenggara Barat, khususnya di SEKOTONG, LEMBAR.
                 Kondisi diperkirakan dapat berlangsung hingga 17 October 2026, 12:00 WITA.</description>
                <guid isPermaLink="false">ntb-test-001</guid>
                <pubDate>Fri, 16 Oct 2026 22:00:00 +0000</pubDate>
            </item>';
            $rss = preg_replace('/<item>/', $ntb.'<item>', $rss, 1);

            array_unshift($quakes['Infogempa']['gempa'], [
                'Tanggal' => '17 Okt 2026', 'Jam' => '03:00:00 WIB', 'DateTime' => '2026-10-16T20:00:00+00:00',
                'Coordinates' => '-8.90,115.90', 'Lintang' => '8.90 LS', 'Bujur' => '115.90 BT',
                'Magnitude' => '6.2', 'Kedalaman' => '10 km',
                'Wilayah' => 'Pusat gempa berada di laut 25 km barat daya Lombok Barat', 'Dirasakan' => 'IV Sekotong, III Mataram',
            ]);
        }

        Http::fake([
            'api.bmkg.go.id/*' => Http::response($this->fixture('prakiraan-cuaca.json')),
            'www.bmkg.go.id/alerts/nowcast/*' => Http::response($rss, 200, ['Content-Type' => 'application/xml']),
            'data.bmkg.go.id/*' => Http::response(json_encode($quakes)),
        ]);
    }

    public function test_import_stores_data_and_respects_minimum_interval(): void
    {
        $this->fakeBmkg(withLocalHazards: false);
        $importer = app(BmkgImporter::class);

        $this->assertSame(['forecast' => 'synced', 'nowcast' => 'synced', 'earthquake' => 'synced'], $importer->refresh());
        $this->assertSame(18, BmkgForecast::count());
        $this->assertSame(0, BmkgAlert::count(), 'Peringatan di luar NTB tidak boleh tersimpan');
        $this->assertSame(15, BmkgEarthquake::count());
        $this->assertSame(0, BmkgEarthquake::where('is_nearby', true)->count(), 'Daftar fixture tidak memuat gempa di sekitar Lombok');

        // Request kedua dalam jeda minimum tidak menyentuh API sama sekali.
        $this->assertSame(['forecast' => 'skipped', 'nowcast' => 'skipped', 'earthquake' => 'skipped'], $importer->refresh());
        Http::assertSentCount(3);

        $this->get(route('sea'))->assertOk()->assertSee('Kondisi Laut &amp; Cuaca', false)->assertSee('Tidak ada gempa dirasakan di Lombok')
            ->assertSee('Tidak ada peringatan dini cuaca BMKG untuk Nusa Tenggara Barat');
    }

    public function test_forecast_becomes_tree_input_and_local_hazards_close_sailing(): void
    {
        $this->fakeBmkg();
        app(BmkgImporter::class)->refresh();

        $alert = BmkgAlert::sole();
        $this->assertSame('tidak_layak', $alert->impact);
        $this->assertSame('SEKOTONG, LEMBAR', $alert->areas);
        $this->assertSame('2026-10-17 12:00', $alert->expires_at->format('Y-m-d H:i'));

        $quake = BmkgEarthquake::where('is_nearby', true)->sole();
        $this->assertLessThan(30, $quake->distance_km);
        $this->assertTrue($quake->at_sea);

        $condition = SeaCondition::whereDate('date', '2026-10-17')->sole();
        $this->assertSame('bmkg', $condition->source);
        $this->assertNotNull($condition->prediction, 'Prakiraan BMKG harus diprediksi pohon keputusan');
        $this->assertSame(Sailing::TIDAK_LAYAK, $condition->bmkg_flag);
        $this->assertSame(Sailing::TIDAK_LAYAK, $condition->decision());
        $this->assertCount(2, $condition->bmkg_reasons);

        // Tanggal yang ditutup tidak bisa dipesan.
        $this->seed(\Database\Seeders\MasterSeeder::class);
        $boat = \App\Models\Boat::first();
        $this->getJson(route('boats.availability', [$boat, 'date' => '2026-10-17']))
            ->assertJson(['decision' => Sailing::TIDAK_LAYAK, 'tone' => 'tidak']);
    }

    public function test_manual_entry_is_not_overwritten_by_bmkg(): void
    {
        SeaCondition::create([
            'date' => '2026-10-18', 'source' => 'manual', 'cuaca' => 'Hujan Lebat', 'wind_knot' => 25, 'wave_m' => 2.8,
            'jarak_pandang' => 'Buruk', 'pasang_surut' => 'Surut', 'angin' => 'Kencang', 'gelombang' => 'Tinggi',
        ]);

        $this->fakeBmkg(withLocalHazards: false);
        app(BmkgImporter::class)->refresh();

        $this->assertSame('Hujan Lebat', SeaCondition::whereDate('date', '2026-10-18')->value('cuaca'));
    }

    public function test_failure_is_recorded_and_retried_after_backoff(): void
    {
        Http::fake(['*' => Http::response('Too Many Requests', 429)]);
        $importer = app(BmkgImporter::class);

        $this->assertSame('failed', $importer->refresh()['forecast']);
        $this->assertSame('gagal', BmkgSync::find('forecast')->status);
        $this->assertFalse($importer->isDue('forecast'));

        Carbon::setTestNow(now()->addMinutes(config('bmkg.retry_after_failure') + 1));
        $this->assertTrue($importer->isDue('forecast'));
    }

    public function test_time_and_category_parsing(): void
    {
        $t = BmkgImporter::parseIndonesianTime('akan terjadi pada 16 October 2026, 18:30 WIB di', 'pada');
        $this->assertSame('2026-10-16 19:30', $t->format('Y-m-d H:i')); // WIB → WITA

        $this->assertSame('Hujan Lebat', \App\Services\Bmkg\BmkgSafety::weatherCategory(95));
        $this->assertSame('Buruk', \App\Services\Bmkg\BmkgSafety::visibilityCategory(1, '< 1 km'));
        $this->assertSame(Sailing::TIDAK_LAYAK, Sailing::worst(Sailing::LAYAK, Sailing::TIDAK_LAYAK, Sailing::WASPADA));
    }
}
