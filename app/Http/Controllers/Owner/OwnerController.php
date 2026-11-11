<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BoatBlock;
use App\Models\SeaCondition;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Halaman pemilik kapal. Dibuat sesederhana mungkin: tombol besar, sedikit pilihan,
 * dan hanya menampilkan kapal milik pemilik yang sedang login.
 */
class OwnerController extends Controller
{
    private const PAID = [Booking::STATUS_CONFIRMED, Booking::STATUS_DONE];

    public function home(Request $request)
    {
        $boats = $request->user()->boats()->orderBy('name')->get();
        $boatIds = $boats->pluck('id');
        $days = collect(range(0, 13))->map(fn ($i) => today()->addDays($i));

        $bookings = Booking::query()->whereIn('boat_id', $boatIds)
            ->whereBetween('trip_date', [today(), today()->addDays(13)])
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->get()->groupBy(fn ($b) => $b->boat_id.'|'.$b->trip_date->toDateString());

        $blocks = BoatBlock::query()->active()->whereIn('boat_id', $boatIds)
            ->whereBetween('date', [today(), today()->addDays(13)])
            ->get()->keyBy(fn ($b) => $b->boat_id.'|'.$b->date->toDateString());

        $conditions = SeaCondition::query()->whereBetween('date', [today(), today()->addDays(13)])->get()
            ->keyBy(fn ($c) => $c->date->toDateString());

        return view('owner.home', [
            'boats' => $boats,
            'days' => $days,
            'bookings' => $bookings,
            'blocks' => $blocks,
            'conditions' => $conditions,
            'monthIncome' => $this->income($boatIds, now()->startOfMonth(), now()->endOfMonth())['total'],
        ]);
    }

    public function createReport(Request $request)
    {
        return view('owner.report', ['boats' => $request->user()->boats()->orderBy('name')->get()]);
    }

    /** Lapor tamu offline: tanggal itu langsung tertutup untuk pemesanan online. */
    public function storeReport(Request $request)
    {
        $boatIds = $request->user()->boats()->pluck('id')->all();

        $data = $request->validate([
            'boat_id' => ['required', Rule::in($boatIds)],
            'date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.today()->addDays(90)->toDateString()],
            'reason' => ['required', Rule::in(array_keys(BoatBlock::REASONS))],
            'passengers' => ['nullable', 'integer', 'min:1', 'max:60'],
            'amount' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'boat_id.required' => 'Pilih kapalnya dulu.',
            'date.required' => 'Pilih tanggalnya dulu.',
            'date.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.',
        ]);

        $boat = $request->user()->boats()->findOrFail($data['boat_id']);

        if ($boat->hasOnlineBookingOn($data['date'])) {
            return back()->withInput()->withErrors([
                'date' => 'Di tanggal ini kapal sudah dipesan tamu online. Jangan terima tamu lain dulu, segera hubungi admin di '.Setting::get('contact_phone').'.',
            ]);
        }
        if ($boat->blockOn($data['date'])) {
            return back()->withInput()->withErrors(['date' => 'Tanggal ini sudah pernah dilaporkan. Tidak perlu lapor lagi.']);
        }

        BoatBlock::create([...$data, 'reported_by' => $request->user()->id]);

        return redirect()->route('owner.home')->with('success',
            'Terima kasih. '.$boat->name.' tanggal '.Carbon::parse($data['date'])->translatedFormat('d F Y').' sudah ditutup, tidak bisa dipesan online.');
    }

    public function cancelReport(Request $request, BoatBlock $block)
    {
        abort_unless($request->user()->boats()->whereKey($block->boat_id)->exists(), 404);
        $block->update(['cancelled_at' => now()]);

        return back()->with('success', 'Laporan dibatalkan. Kapal bisa dipesan online lagi di tanggal itu.');
    }

    /** Pembukuan: uang sewa kapal dari pesanan online yang sudah dibayar + tamu offline yang dilaporkan. */
    public function books(Request $request)
    {
        $month = $request->query('bulan', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $boatIds = $request->user()->boats()->pluck('id');

        return view('owner.books', [
            'start' => $start,
            'income' => $this->income($boatIds, $start, $start->copy()->endOfMonth()),
        ]);
    }

    public function history(Request $request)
    {
        $bookings = Booking::query()->with('boat')
            ->whereIn('boat_id', $request->user()->boats()->pluck('id'))
            ->whereIn('status', self::PAID)
            ->orderByDesc('trip_date')
            ->paginate(20);

        return view('owner.history', compact('bookings'));
    }

    public function guide()
    {
        return view('owner.guide');
    }

    private function income($boatIds, Carbon $from, Carbon $to): array
    {
        $online = Booking::query()->with('boat')->whereIn('boat_id', $boatIds)->whereIn('status', self::PAID)
            ->whereBetween('trip_date', [$from->toDateString(), $to->toDateString()])->orderBy('trip_date')->get();

        $offline = BoatBlock::query()->with('boat')->active()->whereIn('boat_id', $boatIds)->where('reason', 'tamu_offline')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])->orderBy('date')->get();

        return [
            'online' => $online,
            'offline' => $offline,
            'online_total' => $online->sum('trip_price'),
            'offline_total' => $offline->sum('amount'),
            'total' => $online->sum('trip_price') + $offline->sum('amount'),
        ];
    }
}
