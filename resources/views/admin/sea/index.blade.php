<x-layouts.admin title="Kondisi Laut Harian">
    <div class="grid gap-6 xl:grid-cols-[360px_1fr]">
        <form action="{{ route('admin.sea.store') }}" method="post" class="card space-y-4 self-start p-5" x-data="{ wind: {{ old('wind_knot', 8) }}, wave: {{ old('wave_m', 0.4) }} }">
            @csrf
            <div>
                <h2 class="font-display text-lg font-semibold">Catat kondisi laut</h2>
                <p class="text-xs text-stone-500">Tanggal dalam jangkauan prakiraan BMKG (3 hari) terisi otomatis. Isi form ini bila pengamatan langsung di dermaga berbeda; data petugas tidak akan ditimpa BMKG.</p>
            </div>
            <div><label class="label" for="date">Tanggal</label><input id="date" type="date" name="date" value="{{ old('date', today()->toDateString()) }}" class="input" required></div>
            <div><label class="label" for="cuaca">Cuaca</label><select id="cuaca" name="cuaca" class="input">@foreach (\App\Support\Sailing::ATTRIBUTES['cuaca']['values'] as $v)<option @selected(old('cuaca') === $v)>{{ $v }}</option>@endforeach</select></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label" for="wind_knot">Angin (knot)</label>
                    <input id="wind_knot" type="number" step="0.1" min="0" name="wind_knot" x-model="wind" class="input num" required>
                    <p class="mt-1 text-[11px] text-stone-500">= <span x-text="wind < 11 ? 'Lemah' : (wind <= 21 ? 'Sedang' : 'Kencang')"></span></p>
                </div>
                <div>
                    <label class="label" for="wave_m">Gelombang (m)</label>
                    <input id="wave_m" type="number" step="0.05" min="0" name="wave_m" x-model="wave" class="input num" required>
                    <p class="mt-1 text-[11px] text-stone-500">= <span x-text="wave < 0.5 ? 'Tenang' : (wave <= 1.25 ? 'Rendah' : (wave <= 2.5 ? 'Sedang' : 'Tinggi'))"></span></p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="label" for="jarak_pandang">Jarak pandang</label><select id="jarak_pandang" name="jarak_pandang" class="input">@foreach (\App\Support\Sailing::ATTRIBUTES['jarak_pandang']['values'] as $v)<option @selected(old('jarak_pandang') === $v)>{{ $v }}</option>@endforeach</select></div>
                <div><label class="label" for="pasang_surut">Pasang surut</label><select id="pasang_surut" name="pasang_surut" class="input">@foreach (\App\Support\Sailing::ATTRIBUTES['pasang_surut']['values'] as $v)<option @selected(old('pasang_surut', 'Normal') === $v)>{{ $v }}</option>@endforeach</select></div>
            </div>
            <button class="btn-primary w-full">Simpan & prediksi</button>
            <p class="text-xs text-stone-500">
                @if ($model) Model aktif: #{{ $model->id }} {{ $model->algorithm }} (akurasi {{ $model->accuracy }}%) @else <span class="text-coral-700">Belum ada model aktif.</span> @endif
            </p>
        </form>

        <section class="card overflow-x-auto">
            <table class="table-base">
                <thead><tr><th>Tanggal</th><th>Kondisi</th><th>Prediksi pohon</th><th>Aturan BMKG</th><th>Keputusan akhir</th><th></th></tr></thead>
                <tbody>
                    @forelse ($conditions as $c)
                        @php($key = $c->date->toDateString())
                        <tr @class(['bg-sand-50/70' => $c->date->isPast() && ! $c->date->isToday()])>
                            <td class="whitespace-nowrap"><p class="font-semibold">{{ $c->date->translatedFormat('D, d M') }}</p>@if ($c->date->isToday())<span class="chip bg-coral-500 text-white">Hari ini</span>@endif</td>
                            <td class="text-xs leading-relaxed">
                                <span @class(['chip mb-1', 'bg-sea-50 text-sea-800' => $c->source === 'bmkg', 'bg-sand-100 text-stone-700' => $c->source !== 'bmkg'])>{{ $c->source === 'bmkg' ? 'BMKG otomatis' : 'Input petugas' }}</span><br>
                                {{ $c->cuaca }} · {{ $c->wind_knot + 0 }} kn <span class="text-stone-500">({{ $c->angin }})</span><br>
                                {{ $c->wave_m + 0 }} m <span class="text-stone-500">({{ $c->gelombang }})</span> · pandang {{ $c->jarak_pandang }} · {{ $c->pasang_surut }}
                            </td>
                            <td>
                                <x-decision :value="$c->prediction"/>
                                @if ($c->decision_path)
                                    <p class="mt-1 max-w-56 text-[11px] text-stone-500">@foreach ($c->decision_path as $s){{ \App\Support\Sailing::label($s['attribute']) }}={{ $s['value'] }}{{ $loop->last ? '' : ' → ' }}@endforeach</p>
                                @endif
                            </td>
                            <td>
                                @if ($c->bmkg_flag)<x-decision :value="$c->bmkg_flag"/>@else<span class="text-xs text-stone-400">Aman</span>@endif
                                <div class="max-w-60">@include('bmkg._reasons', ['condition' => $c])</div>
                                @if ($c->source !== 'bmkg' && $c->date->gte(today()) && $c->date->lte(today()->addDays(2)))
                                    <form action="{{ route('admin.sea.bmkg', $c) }}" method="post" class="mt-2">@csrf<button class="text-[11px] font-semibold text-sea-700 hover:underline">Pakai data BMKG</button></form>
                                @endif
                            </td>
                            <td x-data="{ edit: false }">
                                <div x-show="!edit" class="flex items-center gap-2">
                                    <x-decision :value="$c->decision()"/>
                                    @if ($c->isOverridden())<span class="text-[10px] font-semibold text-coral-600 uppercase">diubah</span>@endif
                                    <button type="button" class="text-xs font-semibold text-sea-700 hover:underline" @click="edit = true">Ubah</button>
                                </div>
                                @if ($c->note)<p x-show="!edit" class="mt-1 text-[11px] text-stone-500">{{ $c->note }}</p>@endif
                                <form x-show="edit" x-cloak action="{{ route('admin.sea.decide', $c) }}" method="post" class="space-y-2">
                                    @csrf
                                    <select name="final_decision" class="input py-1.5 text-xs">
                                        <option value="">Ikuti prediksi</option>
                                        @foreach (\App\Support\Sailing::DECISIONS as $d)<option @selected($c->final_decision === $d)>{{ $d }}</option>@endforeach
                                    </select>
                                    <input name="note" value="{{ $c->note }}" class="input py-1.5 text-xs" placeholder="Alasan / catatan nakhoda">
                                    <div class="flex gap-2"><button class="btn-sea btn-sm">Simpan</button><button type="button" class="btn-ghost btn-sm" @click="edit = false">Batal</button></div>
                                </form>
                                @if (isset($affected[$key]))
                                    <a href="{{ route('admin.bookings.index', ['tanggal' => $key]) }}" class="mt-2 block rounded-lg bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-800">{{ $affected[$key]->count() }} pesanan perlu dijadwalkan ulang →</a>
                                @endif
                            </td>
                            <td class="text-right">
                                <form action="{{ route('admin.sea.destroy', $c) }}" method="post" onsubmit="return confirm('Hapus data tanggal ini?')">@csrf @method('delete')<button class="text-xs text-stone-400 hover:text-coral-600" aria-label="Hapus"><x-icon name="x" class="size-4"/></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-stone-500">Belum ada data kondisi laut.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</x-layouts.admin>
