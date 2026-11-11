<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SailingRecord;
use App\Support\Sailing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SailingRecordController extends Controller
{
    public function index(Request $request)
    {
        $records = SailingRecord::query()
            ->when($request->query('keputusan'), fn ($q, $k) => $q->where('keputusan', $k))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.records.index', compact('records'));
    }

    public function store(Request $request)
    {
        SailingRecord::create([...$request->validate($this->rules()), 'source' => 'manual']);

        return back()->with('success', 'Data latih ditambahkan. Latih ulang model agar data baru ikut dipelajari.');
    }

    public function destroy(SailingRecord $record)
    {
        $record->delete();

        return back()->with('success', 'Data latih dihapus.');
    }

    /** Impor CSV: recorded_on,cuaca,angin,gelombang,jarak_pandang,pasang_surut,keputusan */
    public function import(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = array_map(fn ($h) => strtolower(trim($h, " \t\n\r\0\x0B\xEF\xBB\xBF")), fgetcsv($handle) ?: []);
        $columns = ['cuaca', 'angin', 'gelombang', 'jarak_pandang', 'pasang_surut', 'keputusan'];

        if (array_diff($columns, $header)) {
            return back()->withErrors(['file' => 'Kolom wajib: '.implode(', ', $columns).'.']);
        }

        $imported = 0;
        $skipped = 0;
        while (($line = fgetcsv($handle)) !== false) {
            if (count($line) !== count($header)) {
                $skipped++;
                continue;
            }
            $row = array_map('trim', array_combine($header, $line));
            $validator = validator($row, $this->rules());
            if ($validator->fails()) {
                $skipped++;
                continue;
            }
            SailingRecord::create([...$validator->validated(), 'source' => 'impor']);
            $imported++;
        }
        fclose($handle);

        return back()->with('success', "{$imported} baris diimpor".($skipped ? ", {$skipped} baris dilewati karena nilainya tidak valid." : '.'));
    }

    public function export()
    {
        $columns = ['recorded_on', ...Sailing::attributeKeys(), Sailing::TARGET];

        return response()->streamDownload(function () use ($columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            SailingRecord::query()->orderBy('id')->each(function ($r) use ($out, $columns) {
                fputcsv($out, array_map(fn ($c) => $c === 'recorded_on' ? $r->recorded_on?->toDateString() : $r->{$c}, $columns));
            });
            fclose($out);
        }, 'data-latih-pelayaran.csv', ['Content-Type' => 'text/csv']);
    }

    private function rules(): array
    {
        $rules = ['recorded_on' => ['nullable', 'date'], 'keputusan' => ['required', Rule::in(Sailing::DECISIONS)]];
        foreach (Sailing::ATTRIBUTES as $key => $attr) {
            $rules[$key] = ['required', Rule::in($attr['values'])];
        }

        return $rules;
    }
}
