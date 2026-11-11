<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use App\Models\Setting;
use App\Support\Seo;

class SitemapController extends Controller
{
    /** Daftar halaman publik untuk Google Search Console. */
    public function sitemap()
    {
        $pages = collect(array_keys(Seo::PAGES))->map(fn ($route) => [
            'loc' => route($route),
            'changefreq' => match ($route) { 'home', 'sea' => 'daily', 'boats.index' => 'weekly', default => 'monthly' },
            'priority' => match ($route) { 'home' => '1.0', 'boats.index', 'sea' => '0.9', default => '0.7' },
            'lastmod' => null,
        ]);

        $boats = Boat::query()->where('is_active', true)->orderBy('name')->get()->map(fn (Boat $b) => [
            'loc' => route('boats.show', $b),
            'changefreq' => 'weekly',
            'priority' => '0.8',
            'lastmod' => $b->updated_at?->toAtomString(),
        ]);

        return response()
            ->view('seo.sitemap', ['urls' => $pages->concat($boats)])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots()
    {
        $lines = Setting::enabled('seo_indexing')
            ? ['User-agent: *', 'Allow: /', 'Disallow: /admin', 'Disallow: /pemilik', 'Disallow: /pesanan', 'Disallow: /checkout', 'Disallow: /profil', '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n")->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
