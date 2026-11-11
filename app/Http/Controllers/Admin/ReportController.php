<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('bulan', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $bookings = Booking::query()->with(['boat', 'meals', 'rentals'])
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_DONE])
            ->whereBetween('trip_date', [$start, $end])
            ->orderBy('trip_date')
            ->get();

        $summary = [
            'trips' => $bookings->count(),
            'passengers' => $bookings->sum('passengers'),
            'trip' => $bookings->sum('trip_price'),
            'rentals' => $bookings->sum('rental_total'),
            'meals' => $bookings->sum('meal_total'),
            'total' => $bookings->sum('total'),
        ];

        $byBoat = $bookings->groupBy('boat.name')->map(fn ($g) => ['trips' => $g->count(), 'total' => $g->sum('total')])->sortByDesc('total');

        return view('admin.reports.index', compact('bookings', 'summary', 'byBoat', 'start'));
    }
}
