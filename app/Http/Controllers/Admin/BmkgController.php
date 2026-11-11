<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Bmkg\BmkgFeed;
use App\Services\Bmkg\BmkgImporter;

class BmkgController extends Controller
{
    public function index(BmkgFeed $feed, BmkgImporter $importer)
    {
        return view('admin.bmkg.index', [
            'feed' => $feed,
            'syncs' => $feed->syncs(),
            'due' => collect(array_keys(config('bmkg.sources')))->mapWithKeys(fn ($s) => [$s => $importer->isDue($s)]),
        ]);
    }

    /** Sinkron manual. Tetap menghormati jeda minimum, kecuali sumber terakhir gagal. */
    public function sync(BmkgImporter $importer)
    {
        $result = $importer->refresh();
        $labels = ['synced' => 'diperbarui', 'skipped' => 'masih segar (jeda belum lewat)', 'failed' => 'gagal'];

        return back()->with('success', collect($result)
            ->map(fn ($status, $source) => config("bmkg.sources.$source.label").': '.$labels[$status])
            ->join(' · '));
    }
}
