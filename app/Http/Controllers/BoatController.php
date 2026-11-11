<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use App\Models\MealPackage;
use App\Models\RentalItem;
use App\Models\SeaCondition;
use App\Support\Sailing;
use App\Support\Seo;
use Illuminate\Http\Request;

class BoatController extends Controller
{
    public function index(Request $request)
    {
        $group = (int) $request->query('rombongan');

        $boats = Boat::query()
            ->withRating()
            ->where('is_active', true)
            ->when($group > 0, fn ($q) => $q->where('capacity', '>=', $group))
            ->orderBy('capacity')
            ->get();

        return view('boats.index', compact('boats', 'group'));
    }

    public function show(Request $request, Boat $boat)
    {
        abort_unless($boat->is_active, 404);

        $draft = $request->session()->get('booking_draft');
        $boat->loadCount('visibleReviews')->loadAvg('visibleReviews', 'rating');

        return view('boats.show', [
            'boat' => $boat,
            'meals' => MealPackage::query()->where('is_active', true)->orderBy('code')->get(),
            'rentals' => RentalItem::query()->where('is_active', true)->orderBy('sort')->get(),
            'draft' => ($draft['boat_id'] ?? null) === $boat->id ? $draft : null,
            'reviews' => $boat->visibleReviews()->with('user')->latest()->take(6)->get(),
            'others' => Boat::query()->withRating()->where('is_active', true)->whereKeyNot($boat->id)->orderBy('capacity')->take(3)->get(),
            'seoTitle' => "{$boat->name}: Sewa Perahu {$boat->capacity} Orang ke 3 Gili Sekotong",
            'seoDescription' => "Sewa perahu {$boat->name} (".strtolower($boat->type).", maks. {$boat->capacity} orang) untuk trip Gili Nanggu, Sudak, Kedis atau mancing dari Dermaga Tawun. ".rupiah($boat->trip_price)." per perahu. ".$boat->description,
            'schema' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Product',
                    'name' => "Sewa Perahu {$boat->name}: Trip 3 Gili Sekotong",
                    'description' => $boat->description,
                    'image' => $boat->photoUrl() ?: Seo::ogImage(),
                    'brand' => ['@type' => 'Brand', 'name' => Seo::siteName()],
                    ...($boat->visible_reviews_count ? ['aggregateRating' => [
                        '@type' => 'AggregateRating',
                        'ratingValue' => round($boat->visible_reviews_avg_rating, 1),
                        'reviewCount' => $boat->visible_reviews_count,
                    ]] : []),
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => $boat->trip_price,
                        'priceCurrency' => 'IDR',
                        'availability' => 'https://schema.org/InStock',
                        'url' => route('boats.show', $boat),
                    ],
                ],
                Seo::breadcrumb([['Beranda', route('home')], ['Perahu', route('boats.index')], [$boat->name, route('boats.show', $boat)]]),
            ],
        ]);
    }

    /** Dipanggil form pemesanan untuk memeriksa tanggal secara langsung. */
    public function availability(Request $request, Boat $boat)
    {
        $data = $request->validate(['date' => ['required', 'date']]);
        $date = $data['date'];

        $condition = SeaCondition::query()->whereDate('date', $date)->first();
        $decision = $condition?->decision();

        return response()->json([
            'booked' => $boat->isBookedOn($date),
            'decision' => $decision,
            'tone' => Sailing::tone($decision),
            'advice' => Sailing::advice($decision),
            'reasons' => collect($condition?->bmkg_reasons)->pluck('text')->all(),
            'source' => $condition?->source,
        ]);
    }
}
