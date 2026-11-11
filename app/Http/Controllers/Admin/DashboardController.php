<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\DecisionTreeModel;
use App\Models\SeaCondition;

class DashboardController extends Controller
{
    public function index()
    {
        $active = [Booking::STATUS_CONFIRMED, Booking::STATUS_DONE];

        return view('admin.dashboard', [
            'stats' => [
                'review' => Booking::query()->where('status', Booking::STATUS_REVIEW)->count(),
                'today' => Booking::query()->whereDate('trip_date', today())->whereIn('status', $active)->count(),
                'month_revenue' => Booking::query()->whereIn('status', $active)
                    ->whereBetween('trip_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
                'month_trips' => Booking::query()->whereIn('status', $active)
                    ->whereBetween('trip_date', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            ],
            'needsReview' => Booking::query()->with('boat')->where('status', Booking::STATUS_REVIEW)->oldest('paid_at')->take(6)->get(),
            'upcoming' => Booking::query()->with('boat')
                ->whereBetween('trip_date', [today(), today()->addDays(6)])
                ->whereNotIn('status', [Booking::STATUS_CANCELLED])
                ->orderBy('trip_date')->orderBy('departure_time')->get(),
            'conditions' => SeaCondition::query()->whereBetween('date', [today(), today()->addDays(6)])->get()
                ->keyBy(fn ($c) => $c->date->toDateString()),
            'model' => DecisionTreeModel::active(),
            'blocks' => \App\Models\BoatBlock::query()->with('boat')->active()
                ->whereBetween('date', [today()->toDateString(), today()->addDays(6)->toDateString()])->orderBy('date')->get(),
        ]);
    }
}
