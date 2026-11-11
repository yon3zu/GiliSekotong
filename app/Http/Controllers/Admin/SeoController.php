<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SeoController extends Controller
{
    public function edit()
    {
        $values = collect(array_keys(Setting::SEO_DEFAULTS))->mapWithKeys(fn ($k) => [$k => Setting::get($k)]);

        return view('admin.seo', [
            'values' => $values,
            'pages' => Seo::PAGES,
            'checks' => $this->checks($values),
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'seo_site_name' => ['required', 'string', 'max:60'],
            'seo_google_verification' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9_\-]*$/'],
            'seo_bing_verification' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9_\-]*$/'],
            'seo_ga4_id' => ['nullable', 'regex:/^G-[A-Z0-9]{4,20}$/'],
            'og_image' => ['nullable', 'image', 'max:2048', 'dimensions:min_width=600,min_height=315'],
            'biz_type' => ['required', Rule::in(['TravelAgency', 'LocalBusiness', 'TouristInformationCenter'])],
            'biz_street' => ['nullable', 'string', 'max:160'],
            'biz_locality' => ['nullable', 'string', 'max:80'],
            'biz_region' => ['nullable', 'string', 'max:80'],
            'biz_postal_code' => ['nullable', 'digits:5'],
            'biz_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'biz_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'biz_opening_hours' => ['nullable', 'string', 'max:60'],
            'biz_price_range' => ['nullable', 'string', 'max:60'],
            'biz_maps_url' => ['nullable', 'url', 'max:255'],
            'biz_instagram' => ['nullable', 'url', 'max:255'],
            'biz_facebook' => ['nullable', 'url', 'max:255'],
            'biz_tiktok' => ['nullable', 'url', 'max:255'],
        ];
        foreach (Seo::PAGES as $page) {
            $rules["seo_page_{$page['key']}_title"] = ['required', 'string', 'max:70'];
            $rules["seo_page_{$page['key']}_description"] = ['required', 'string', 'max:170'];
        }

        $data = $request->validate($rules, [
            'seo_google_verification.regex' => 'Tempel hanya kode verifikasinya (isi atribut content), bukan seluruh tag <meta>.',
            'seo_ga4_id.regex' => 'Format ID Google Analytics 4 adalah G-XXXXXXX.',
            'og_image.dimensions' => 'Gambar berbagi minimal 600×315 piksel (disarankan 1200×630).',
        ]);

        unset($data['og_image']);
        $data['seo_indexing'] = $request->boolean('seo_indexing') ? '1' : '0';

        if ($request->hasFile('og_image')) {
            if ($old = Setting::get('seo_og_image')) {
                Storage::disk('public')->delete($old);
            }
            $data['seo_og_image'] = $request->file('og_image')->store('seo', 'public');
        } elseif ($request->boolean('remove_og_image')) {
            Storage::disk('public')->delete((string) Setting::get('seo_og_image'));
            $data['seo_og_image'] = '';
        }

        Setting::put(array_map(fn ($v) => $v ?? '', $data));

        return back()->with('success', 'Pengaturan SEO disimpan.');
    }

    /** Daftar periksa sederhana agar admin tahu apa yang masih kurang. */
    private function checks($values): array
    {
        $appUrl = config('app.url');
        $pagesOk = collect(Seo::PAGES)->every(function ($p) use ($values) {
            $t = mb_strlen((string) $values["seo_page_{$p['key']}_title"]);
            $d = mb_strlen((string) $values["seo_page_{$p['key']}_description"]);

            return $t >= 30 && $t <= 65 && $d >= 110 && $d <= 160;
        });

        return [
            ['Situs boleh diindeks mesin pencari', $values['seo_indexing'] === '1', 'Aktifkan "Izinkan Google mengindeks" di bawah.'],
            ['Domain produksi memakai HTTPS', str_starts_with($appUrl, 'https://') && ! str_contains($appUrl, 'localhost'), "APP_URL saat ini: {$appUrl}. Ubah di .env server menjadi https://gilisekotong.com."],
            ['Judul 30–65 karakter & deskripsi 110–160 karakter di semua halaman', $pagesOk, 'Perbaiki judul/deskripsi yang ditandai merah pada tabel halaman.'],
            ['Verifikasi Google Search Console', filled($values['seo_google_verification']), 'Daftarkan gilisekotong.com di Search Console, lalu tempel kode verifikasinya.'],
            ['Google Analytics 4 terpasang', filled($values['seo_ga4_id']), 'Opsional, untuk melihat jumlah pengunjung dan asal pencariannya.'],
            ['Tautan Google Maps (Profil Bisnis)', filled($values['biz_maps_url']), 'Buat Google Business Profile untuk Dermaga Tawun, lalu tempel tautannya.'],
            ['Kode pos & koordinat bisnis', filled($values['biz_postal_code']) && filled($values['biz_lat']), 'Lengkapi kode pos dan cek koordinat dermaga di Google Maps.'],
        ];
    }
}
