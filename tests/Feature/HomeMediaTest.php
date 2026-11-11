<?php

namespace Tests\Feature;

use App\Models\MediaItem;
use App\Models\User;
use Database\Seeders\MasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['services.ffmpeg' => '/tidak/ada/ffmpeg']);
        $this->seed(MasterSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_home_shows_illustration_until_media_uploaded(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('Keindahan 3 Gili Sekotong')
            ->assertSee('Gili Nanggu')
            ->assertDontSee('x-data="heroSlider', false);
    }

    public function test_admin_uploads_photo_which_is_resized_and_shown_on_home(): void
    {
        $this->actingAs($this->admin())->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('gili.jpg', 3200, 1800),
            'title' => 'Pasir putih Gili Kedis', 'location' => 'Gili Kedis', 'placement' => 'hero', 'sort' => 1, 'is_active' => '1',
        ])->assertRedirect(route('admin.media.index'));

        $item = MediaItem::sole();
        $this->assertSame('image', $item->type);
        $this->assertStringEndsWith('.jpg', $item->path);
        [$w, $h] = getimagesize(Storage::disk('public')->path($item->path));
        $this->assertSame(2000, $w);
        $this->assertSame(1125, $h);

        $this->get(route('home'))->assertOk()
            ->assertSee('x-data="heroSlider', false)
            ->assertSee('alt="Pasir putih Gili Kedis"', false)
            ->assertSee('<meta property="og:image" content="'.$item->url().'">', false);
    }

    public function test_video_upload_goes_to_gallery_with_optional_poster(): void
    {
        $video = new UploadedFile(base_path('tests/Fixtures/media/clip.mp4'), 'clip.mp4', 'video/mp4', null, true);

        $this->actingAs($this->admin())->post(route('admin.media.store'), [
            'file' => $video, 'title' => 'Snorkeling di Gili Nanggu', 'location' => 'Gili Nanggu', 'placement' => 'gallery', 'sort' => 1, 'is_active' => '1',
        ])->assertSessionHasNoErrors()->assertSessionHas('success', fn ($m) => str_contains($m, 'sampul'));

        $item = MediaItem::sole();
        $this->assertSame('video', $item->type);
        $this->assertNull($item->poster_path);
        Storage::disk('public')->assertExists($item->path);

        $this->put(route('admin.media.update', $item), [
            'poster' => UploadedFile::fake()->image('sampul.jpg', 1280, 720),
            'title' => $item->title, 'location' => 'Gili Nanggu', 'placement' => 'gallery', 'sort' => 1, 'is_active' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertNotNull($item->fresh()->poster_path);

        $this->get(route('home'))->assertSee('Snorkeling di Gili Nanggu')
            ->assertSee($item->fresh()->posterUrl(), false)
            ->assertSee(basename($item->path), false); // video tersedia di data lightbox
    }

    public function test_hidden_media_and_validation(): void
    {
        $this->actingAs($this->admin())->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            'title' => 'Salah', 'placement' => 'gallery', 'sort' => 1,
        ])->assertSessionHasErrors('file');

        $this->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('a.jpg', 800, 600), 'title' => 'Disembunyikan', 'placement' => 'gallery', 'sort' => 1,
        ]);
        $this->get(route('home'))->assertDontSee('Disembunyikan');

        $item = MediaItem::sole();
        $this->delete(route('admin.media.destroy', $item));
        Storage::disk('public')->assertMissing($item->path);
    }

    public function test_only_staff_manage_gallery(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'pemesan']))->get(route('admin.media.index'))->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'pemilik']))->get(route('admin.media.index'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('admin.media.index'))->assertOk()->assertSee('Hero beranda');
    }
}
