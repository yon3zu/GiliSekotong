<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Boat;
use App\Models\BoatBlock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Laporan tamu offline dari pemilik kapal; admin juga bisa mencatatkannya (mis. pemilik lapor lewat telepon). */
class BoatBlockController extends Controller
{
    public function index()
    {
        return view('admin.blocks.index', [
            'upcoming' => BoatBlock::query()->with(['boat.owner', 'reporter'])->active()
                ->whereDate('date', '>=', today())->orderBy('date')->get(),
            'recent' => BoatBlock::query()->with(['boat', 'reporter'])
                ->where(fn ($q) => $q->whereDate('date', '<', today())->orWhereNotNull('cancelled_at'))
                ->latest('updated_at')->take(20)->get(),
            'boats' => Boat::query()->with('owner')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'boat_id' => ['required', 'exists:boats,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['required', Rule::in(array_keys(BoatBlock::REASONS))],
            'passengers' => ['nullable', 'integer', 'min:1', 'max:60'],
            'amount' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $boat = Boat::findOrFail($data['boat_id']);
        if ($boat->hasOnlineBookingOn($data['date'])) {
            return back()->withInput()->withErrors(['date' => "{$boat->name} sudah punya pesanan online di tanggal itu. Jadwalkan ulang pesanan tersebut dulu bila perlu."]);
        }
        if ($boat->blockOn($data['date'])) {
            return back()->withInput()->withErrors(['date' => 'Tanggal ini sudah ditutup sebelumnya.']);
        }

        BoatBlock::create([...$data, 'reported_by' => $request->user()->id]);

        return back()->with('success', "{$boat->name} ditutup untuk pemesanan online pada tanggal tersebut.");
    }

    public function cancel(BoatBlock $block)
    {
        $block->update(['cancelled_at' => now()]);

        return back()->with('success', 'Penutupan dibatalkan. Kapal bisa dipesan online lagi.');
    }
}
