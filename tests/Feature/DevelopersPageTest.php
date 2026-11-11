<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DevelopersPageTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function form(array $attrs = []): array
    {
        return ['name' => 'Siti Aminah', 'tag' => 'UI Designer', 'kind' => 'mahasiswa', 'sort' => 1, 'is_active' => '1', ...$attrs];
    }

    public function test_page_orders_lecturers_then_lead_then_students(): void
    {
        Developer::create(['name' => 'Siti Aminah', 'kind' => 'mahasiswa', 'sort' => 1]);
        Developer::create(['name' => 'Dr. Dosen Satu', 'tag' => 'Dosen Pembimbing', 'kind' => 'dosen', 'sort' => 1]);
        Developer::create(['name' => 'Tersembunyi', 'kind' => 'mahasiswa', 'sort' => 2, 'is_active' => false]);

        $this->get(route('developers'))->assertOk()
            ->assertSeeInOrder(['Dr. Dosen Satu', 'Dosen Pembimbing', 'Bayu Jumhari Cahyono S', 'Full Stack Web Developer', 'Siti Aminah'])
            ->assertDontSee('Tersembunyi');
    }

    public function test_lead_is_shown_even_with_empty_table(): void
    {
        $this->get(route('developers'))->assertOk()->assertSee('Bayu Jumhari Cahyono S')->assertSee('Full Stack Web Developer');
    }

    public function test_only_superadmin_manages_team(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.developers.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.developers.store'), $this->form())->assertForbidden();
        $this->assertSame(0, Developer::count());
    }

    public function test_superadmin_adds_edits_and_removes_member_with_photo(): void
    {
        Storage::fake('public');
        $root = $this->superadmin();

        $this->actingAs($root)->post(route('admin.developers.store'), $this->form([
            'photo' => UploadedFile::fake()->image('siti.jpg', 1600, 1600),
        ]))->assertRedirect(route('admin.developers.index'));
        $member = Developer::sole();
        Storage::disk('public')->assertExists($member->photo);
        $this->assertSame(800, getimagesize(Storage::disk('public')->path($member->photo))[0]);

        $this->actingAs($root)->put(route('admin.developers.update', $member), $this->form(['name' => 'Siti A.', 'remove_photo' => '1']))->assertRedirect();
        Storage::disk('public')->assertMissing($member->photo);
        $this->assertNull($member->fresh()->photo);
        $this->assertSame('Siti A.', $member->fresh()->name);

        $this->actingAs($root)->delete(route('admin.developers.destroy', $member))->assertRedirect();
        $this->assertSame(0, Developer::count());
    }

    public function test_superadmin_sets_banner_and_lead_photo_shown_on_page(): void
    {
        Storage::fake('public');
        $root = $this->superadmin();

        $this->actingAs($root)->post(route('admin.developers.appearance'), [
            'banner' => UploadedFile::fake()->image('tim.jpg', 2400, 1000),
            'lead_photo' => UploadedFile::fake()->image('bayu.png', 900, 900),
        ])->assertRedirect();

        $banner = Setting::get('dev_banner');
        $lead = Setting::get('dev_lead_photo');
        Storage::disk('public')->assertExists([$banner, $lead]);
        $this->get(route('developers'))->assertSee($banner)->assertSee($lead);

        $this->actingAs($root)->post(route('admin.developers.appearance'), ['remove_banner' => '1'])->assertRedirect();
        Storage::disk('public')->assertMissing($banner);
        $this->get(route('developers'))->assertDontSee($banner)->assertSee($lead);
    }

    public function test_page_is_in_sitemap_and_header_links_to_it(): void
    {
        $this->get(route('sitemap'))->assertSee(route('developers'));
        $this->get(route('developers'))->assertSee('images/aksara-badge.webp')->assertSee('AKSARA Universitas Bumigora');
    }
}
