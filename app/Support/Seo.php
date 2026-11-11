<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Menyusun tag SEO untuk setiap halaman publik: judul, deskripsi, canonical,
 * Open Graph/Twitter, robots, dan structured data (JSON-LD) schema.org.
 * Judul & deskripsi per halaman bisa diubah dari Admin → SEO.
 */
class Seo
{
    /** Halaman yang judul/deskripsinya diatur dari panel admin: nama route → kunci pengaturan. */
    public const PAGES = [
        'home' => ['key' => 'home', 'label' => 'Beranda'],
        'boats.index' => ['key' => 'boats', 'label' => 'Daftar Perahu'],
        'destinations' => ['key' => 'destinations', 'label' => 'Destinasi 3 Gili'],
        'sea' => ['key' => 'sea', 'label' => 'Kondisi Laut & Cuaca'],
        'howto' => ['key' => 'howto', 'label' => 'Cara Pesan'],
        'about' => ['key' => 'about', 'label' => 'Tentang Kami'],
        'contact' => ['key' => 'contact', 'label' => 'Kontak & FAQ'],
        'developers' => ['key' => 'developers', 'label' => 'Tim Pengembang'],
    ];

    /** Halaman yang tidak boleh muncul di hasil pencarian. */
    private const NOINDEX_ROUTES = ['login', 'register', 'booking.*', 'orders.*', 'profile.*', 'owner.*', 'admin.*'];

    public static function siteName(): string
    {
        return Setting::get('seo_site_name') ?: config('app.name');
    }

    /**
     * @param  string|null  $title  judul bawaan dari view (dipakai bila admin belum mengisi)
     * @param  string|null  $description  deskripsi bawaan dari view
     * @param  array  $schema  JSON-LD tambahan khusus halaman
     */
    public static function meta(?string $title = null, ?string $description = null, ?string $image = null, array $schema = []): array
    {
        $route = Route::currentRouteName();
        $page = self::PAGES[$route] ?? null;
        $site = self::siteName();

        $title = ($page ? Setting::get("seo_page_{$page['key']}_title") : null)
            ?: ($title ? "{$title} | {$site}" : Setting::get('seo_page_home_title'));
        $description = ($page ? Setting::get("seo_page_{$page['key']}_description") : null)
            ?: $description
            ?: Setting::get('seo_page_home_description');

        return [
            'title' => $title,
            'description' => Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($description))), 160, '…'),
            'canonical' => self::canonical(),
            'robots' => self::indexable() ? 'index, follow, max-image-preview:large' : 'noindex, nofollow',
            'image' => $image ?: self::ogImage(),
            'site' => $site,
            'type' => $route === 'home' ? 'website' : 'article',
            'schema' => array_values(array_filter([$route === 'home' ? self::websiteSchema() : null, self::businessSchema(), ...$schema])),
            'google' => Setting::get('seo_google_verification'),
            'bing' => Setting::get('seo_bing_verification'),
            'ga4' => preg_match('/^G-[A-Z0-9]+$/', (string) Setting::get('seo_ga4_id')) ? Setting::get('seo_ga4_id') : null,
        ];
    }

    public static function indexable(): bool
    {
        return Setting::enabled('seo_indexing') && ! request()->routeIs(...self::NOINDEX_ROUTES);
    }

    /** URL tanpa query string (mis. ?rombongan=4 tidak dianggap halaman berbeda). */
    public static function canonical(): string
    {
        return url()->current();
    }

    public static function ogImage(): string
    {
        $custom = Setting::get('seo_og_image');

        if ($custom) {
            return asset('storage/'.$custom);
        }

        // Bila belum ada gambar berbagi khusus, pakai foto hero pertama, lalu gambar bawaan.
        $hero = \App\Models\MediaItem::query()->shown('hero')->get()->first(fn ($m) => $m->stillUrl());

        return $hero?->stillUrl() ?? asset('images/og-gilisekotong.png');
    }

    public static function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => self::siteName(),
            'url' => route('home'),
            'inLanguage' => 'id-ID',
        ];
    }

    /** Profil usaha lokal: membantu Google memahami lokasi, kontak, dan jam buka. */
    public static function businessSchema(): array
    {
        $sameAs = array_values(array_filter([
            Setting::get('biz_maps_url'), Setting::get('biz_instagram'), Setting::get('biz_facebook'), Setting::get('biz_tiktok'),
        ]));

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => Setting::get('biz_type') ?: 'TravelAgency',
            '@id' => route('home').'#bisnis',
            'name' => self::siteName(),
            'url' => route('home'),
            'logo' => asset('images/logo-gilisekotong.png'),
            'image' => self::ogImage(),
            'description' => Setting::get('seo_page_home_description'),
            'telephone' => Setting::get('contact_phone'),
            'email' => Setting::get('contact_email'),
            'priceRange' => Setting::get('biz_price_range'),
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => Setting::get('biz_street'),
                'addressLocality' => Setting::get('biz_locality'),
                'addressRegion' => Setting::get('biz_region'),
                'postalCode' => Setting::get('biz_postal_code'),
                'addressCountry' => 'ID',
            ]),
            'geo' => is_numeric(Setting::get('biz_lat')) && is_numeric(Setting::get('biz_lng')) ? [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) Setting::get('biz_lat'),
                'longitude' => (float) Setting::get('biz_lng'),
            ] : null,
            'openingHours' => Setting::get('biz_opening_hours'),
            'areaServed' => ['Sekotong', 'Lombok Barat', 'Gili Nanggu', 'Gili Sudak', 'Gili Kedis'],
            'sameAs' => $sameAs ?: null,
        ]);
    }

    /** Tiga gili tujuan trip sebagai daftar tempat wisata. */
    public static function destinationsSchema(): array
    {
        $gili = [
            ['Gili Nanggu', 'Pulau kecil berpasir putih dengan terumbu karang dangkal untuk snorkeling.'],
            ['Gili Sudak', 'Pulau dengan warung makan tepi pantai, tempat makan siang rombongan.'],
            ['Gili Kedis', 'Pulau mungil yang bisa dikelilingi berjalan kaki dalam beberapa menit.'],
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Destinasi trip 3 gili Sekotong',
            'itemListElement' => array_map(fn ($g, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'item' => [
                    '@type' => 'TouristAttraction',
                    'name' => $g[0],
                    'description' => $g[1],
                    'containedInPlace' => ['@type' => 'Place', 'name' => 'Sekotong, Lombok Barat, Nusa Tenggara Barat'],
                ],
            ], $gili, array_keys($gili)),
        ];
    }

    public static function breadcrumb(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($item, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item[0],
                'item' => $item[1],
            ])->all(),
        ];
    }

    public static function json(array $schema): string
    {
        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    }
}
