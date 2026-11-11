<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use App\Models\Developer;
use App\Models\MealPackage;
use App\Models\MediaItem;
use App\Models\RentalItem;
use App\Models\Review;
use App\Models\SeaCondition;
use App\Models\Setting;
use App\Services\Bmkg\BmkgFeed;

class PageController extends Controller
{
    public function home(BmkgFeed $feed)
    {
        return view('pages.home', [
            'boats' => Boat::query()->withRating()->where('is_active', true)->orderBy('capacity')->take(3)->get(),
            'reviews' => Review::query()->visible()->whereNotNull('comment')->with(['user', 'boat'])->where('rating', '>=', 4)->latest()->take(3)->get(),
            'meals' => MealPackage::query()->where('is_active', true)->orderBy('code')->get(),
            'rentals' => RentalItem::query()->where('is_active', true)->orderBy('sort')->get(),
            'today' => SeaCondition::query()->whereDate('date', '>=', today())->orderBy('date')->first(),
            'alerts' => $feed->activeAlerts()->where('impact', '!=', 'info'),
            'heroMedia' => MediaItem::query()->shown('hero')->get(),
            'gallery' => $gallery = MediaItem::query()->shown('gallery')->take(9)->get(),
            'galleryData' => $gallery->map(fn (MediaItem $m) => [
                'src' => $m->url(), 'poster' => $m->posterUrl(), 'video' => $m->isVideo(), 'title' => $m->title, 'location' => $m->location,
            ])->values(),
            'recentQuake' => $feed->nearbyQuakes()->first(fn ($q) => $q->occurred_at->gt(now()->subDay())),
        ]);
    }

    public function destinations()
    {
        return view('pages.destinations');
    }

    public function sea(BmkgFeed $feed)
    {
        $conditions = SeaCondition::query()
            ->whereBetween('date', [today(), today()->addDays(13)])
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($c) => $c->date->toDateString());

        return view('pages.sea', [
            'conditions' => $conditions,
            'current' => $feed->current(),
            'forecast' => $feed->forecastByDay(),
            'alerts' => $feed->recentAlerts(2),
            'quakes' => $feed->nearbyQuakes(),
            'updated' => $feed->lastUpdated(),
        ]);
    }

    public function howto()
    {
        return view('pages.howto', [
            'meals' => MealPackage::query()->where('is_active', true)->orderBy('code')->get(),
            'rentals' => RentalItem::query()->where('is_active', true)->orderBy('sort')->get(),
        ]);
    }

    public function about()
    {
        return view('pages.about', ['boatCount' => Boat::query()->where('is_active', true)->count()]);
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function developers()
    {
        return view('pages.developers', [
            'lecturers' => Developer::query()->shown('dosen')->get(),
            'students' => Developer::query()->shown('mahasiswa')->get(),
            'banner' => Setting::get('dev_banner'),
            'leadPhoto' => Setting::get('dev_lead_photo'),
        ]);
    }
}
