<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\DecisionTreeModel;
use App\Models\SeaCondition;
use App\Services\Bmkg\BmkgSafety;
use App\Services\SailingModelTrainer;
use App\Support\Sailing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeaConditionController extends Controller
{
    public function index()
    {
        $conditions = SeaCondition::query()->with('model')
            ->whereDate('date', '>=', today()->subDays(7))
            ->orderBy('date')
            ->get();

        $affected = Booking::query()
            ->whereIn('trip_date', $conditions->filter->blocksBooking()->pluck('date'))
            ->whereNotIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_DONE])
            ->with('boat')
            ->get()
            ->groupBy(fn ($b) => $b->trip_date->toDateString());

        return view('admin.sea.index', [
            'conditions' => $conditions,
            'affected' => $affected,
            'model' => DecisionTreeModel::active(),
        ]);
    }

    public function store(Request $request, SailingModelTrainer $trainer, BmkgSafety $safety)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'cuaca' => ['required', Rule::in(Sailing::ATTRIBUTES['cuaca']['values'])],
            'wind_knot' => ['required', 'numeric', 'min:0', 'max:80'],
            'wave_m' => ['required', 'numeric', 'min:0', 'max:9'],
            'jarak_pandang' => ['required', Rule::in(Sailing::ATTRIBUTES['jarak_pandang']['values'])],
            'pasang_surut' => ['required', Rule::in(Sailing::ATTRIBUTES['pasang_surut']['values'])],
        ]);

        $condition = SeaCondition::forDate($data['date']);
        $condition->fill([
            ...$data,
            'source' => 'manual',
            'angin' => Sailing::windCategory((float) $data['wind_knot']),
            'gelombang' => Sailing::waveCategory((float) $data['wave_m']),
            'final_decision' => null,
            'updated_by' => $request->user()->id,
        ]);
        [$condition->bmkg_flag, $reasons] = $safety->evaluate($condition->date);
        $condition->bmkg_reasons = $reasons ?: null;
        $trainer->applyPrediction($condition)->save();

        $message = $condition->prediction
            ? "Prediksi pohon keputusan: {$condition->prediction}."
            : 'Data tersimpan, tetapi belum ada model aktif untuk memprediksi.';

        return back()->with('success', $message);
    }

    /** Admin/nakhoda tetap bisa mengambil keputusan akhir yang berbeda dari prediksi. */
    public function decide(Request $request, SeaCondition $seaCondition)
    {
        $data = $request->validate([
            'final_decision' => ['nullable', Rule::in(Sailing::DECISIONS)],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $seaCondition->update([...$data, 'updated_by' => $request->user()->id]);

        return back()->with('success', 'Keputusan akhir disimpan.');
    }

    /** Kembalikan tanggal ini ke data otomatis prakiraan BMKG. */
    public function useBmkg(SeaCondition $seaCondition, BmkgSafety $safety)
    {
        if (! $safety->dailySummary($seaCondition->date)) {
            return back()->withErrors(['date' => 'Prakiraan BMKG untuk tanggal ini belum tersedia (BMKG hanya memuat 3 hari ke depan).']);
        }

        $seaCondition->update(['source' => 'bmkg', 'note' => null, 'final_decision' => null]);
        $safety->apply();

        return back()->with('success', 'Tanggal ini kembali memakai prakiraan BMKG.');
    }

    public function destroy(SeaCondition $seaCondition)
    {
        $seaCondition->delete();

        return back()->with('success', 'Data kondisi laut dihapus.');
    }
}
