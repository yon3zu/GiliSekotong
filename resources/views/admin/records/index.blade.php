<x-layouts.admin title="Data Latih">
    <x-slot:actions>
        <a href="{{ route('admin.records.export') }}" class="btn-ghost btn-sm">Ekspor CSV</a>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
        <section>
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.records.index') }}" @class(['chip px-3 py-1 ring-1', 'bg-sea-800 text-white ring-sea-800' => ! request('keputusan'), 'bg-white ring-sand-200' => request('keputusan')])>Semua · {{ $records->total() }}</a>
                @foreach (\App\Support\Sailing::DECISIONS as $d)
                    <a href="{{ route('admin.records.index', ['keputusan' => $d]) }}" @class(['chip px-3 py-1 ring-1', 'bg-sea-800 text-white ring-sea-800' => request('keputusan') === $d, 'bg-white ring-sand-200' => request('keputusan') !== $d])>{{ $d }}</a>
                @endforeach
            </div>
            <div class="card overflow-x-auto">
                <table class="table-base">
                    <thead><tr><th>#</th><th>Tanggal</th>@foreach (\App\Support\Sailing::ATTRIBUTES as $a)<th>{{ $a['label'] }}</th>@endforeach<th>Keputusan</th><th>Sumber</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($records as $r)
                            <tr>
                                <td class="num text-stone-400">{{ $r->id }}</td>
                                <td class="whitespace-nowrap">{{ $r->recorded_on?->format('d/m/Y') ?? '—' }}</td>
                                @foreach (\App\Support\Sailing::attributeKeys() as $k)<td>{{ $r->{$k} }}</td>@endforeach
                                <td><x-decision :value="$r->keputusan"/></td>
                                <td class="text-xs text-stone-500">{{ $r->source }}</td>
                                <td><form action="{{ route('admin.records.destroy', $r) }}" method="post" onsubmit="return confirm('Hapus baris ini?')">@csrf @method('delete')<button class="text-stone-400 hover:text-coral-600" aria-label="Hapus"><x-icon name="x" class="size-4"/></button></form></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $records->links() }}</div>
        </section>

        <aside class="space-y-4">
            <form action="{{ route('admin.records.store') }}" method="post" class="card space-y-3 p-5">
                @csrf
                <h2 class="font-display text-lg font-semibold">Tambah satu data</h2>
                <div class="grid grid-cols-[110px_1fr] items-center gap-2">
                    <label class="text-xs font-semibold" for="recorded_on">Tanggal</label><input id="recorded_on" type="date" name="recorded_on" class="input py-1.5">
                    @foreach (\App\Support\Sailing::ATTRIBUTES as $key => $attr)
                        <label class="text-xs font-semibold" for="r_{{ $key }}">{{ $attr['label'] }}</label>
                        <select id="r_{{ $key }}" name="{{ $key }}" class="input py-1.5">@foreach ($attr['values'] as $v)<option>{{ $v }}</option>@endforeach</select>
                    @endforeach
                    <label class="text-xs font-semibold" for="keputusan">Keputusan</label>
                    <select id="keputusan" name="keputusan" class="input py-1.5">@foreach (\App\Support\Sailing::DECISIONS as $d)<option>{{ $d }}</option>@endforeach</select>
                </div>
                <button class="btn-sea w-full">Tambah</button>
            </form>

            <form action="{{ route('admin.records.import') }}" method="post" enctype="multipart/form-data" class="card space-y-3 p-5">
                @csrf
                <h2 class="font-display text-lg font-semibold">Impor CSV</h2>
                <p class="text-xs leading-relaxed text-stone-500">Header: <code class="rounded bg-sand-100 px-1 break-all">recorded_on,cuaca,angin,gelombang,jarak_pandang,pasang_surut,keputusan</code>. Nilai harus sama persis dengan kategori di formulir (mis. <em>Hujan Ringan</em>, <em>Tidak Layak</em>).</p>
                <input type="file" name="file" accept=".csv,text/csv" required class="block w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-sand-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold">
                <button class="btn-ghost w-full">Impor</button>
            </form>

            <p class="rounded-xl bg-amber-50 p-4 text-xs leading-relaxed text-amber-900 ring-1 ring-amber-200">
                Data bersumber <strong>contoh</strong> dibuat otomatis untuk uji coba. Untuk penelitian, ganti dengan catatan pelayaran asli dari nakhoda/syahbandar dan data BMKG, lalu latih ulang model.
            </p>
        </aside>
    </div>
</x-layouts.admin>
