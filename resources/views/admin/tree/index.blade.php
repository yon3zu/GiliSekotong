<x-layouts.admin title="Pohon Keputusan">
    @php($canTrain = auth()->user()->isSuperadmin())
    @error('train')<div class="mb-6 rounded-xl bg-coral-50 px-4 py-3 text-sm text-coral-700">{{ $message }}</div>@enderror

    <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
        <div class="min-w-0 space-y-6">
        <section class="card p-5">
            @if ($model)
                @php($m = $model->metrics)
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="font-display text-lg font-semibold">Model #{{ $model->id }} · {{ $model->algorithm }}</h2>
                    @if ($model->is_active)<span class="chip tone-layak">Aktif</span>@else<span class="chip tone-none">Arsip</span>@endif
                    <span class="text-xs text-stone-500">dilatih {{ $model->created_at->translatedFormat('d M Y H:i') }}{{ $model->trainer ? ' oleh '.$model->trainer->name : '' }}</span>
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ([
                        ['Akurasi uji', $model->accuracy.'%'],
                        ['Akurasi latih', $m['train_accuracy'].'%'],
                        [$m['cross_validation']['k'].'-fold CV', $m['cross_validation']['mean'].'%'],
                        ['Data latih / uji', $model->training_size.' / '.$model->testing_size],
                        ['Kedalaman', $m['depth'].(isset($m['min_samples']) ? ' · min '.$m['min_samples'] : '')],
                        ['Jumlah aturan', count($model->rules)],
                    ] as [$label, $value])
                        <div class="rounded-xl bg-sand-50 p-3"><dt class="text-[11px] text-stone-500">{{ $label }}</dt><dd class="num font-display text-lg font-semibold whitespace-nowrap">{{ $value }}</dd></div>
                    @endforeach
                </dl>
                @if (! $model->is_active && $canTrain)
                    <form action="{{ route('admin.tree.activate', $model) }}" method="post" class="mt-4">@csrf<button class="btn-sea btn-sm">Jadikan model aktif</button></form>
                @endif
            @else
                <p class="text-sm text-stone-600">Belum ada model. {{ $canTrain ? 'Latih model dari data latih di panel samping.' : 'Minta super admin melatih model.' }}</p>
            @endif
        </section>
        @if ($model)
        <section class="card p-5">
            <div x-data="treeView" class="contents">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <h2 class="font-display text-lg font-semibold">Struktur pohon</h2>
                        <p class="text-xs text-stone-500">Simpul gelap = atribut uji (GR = gain ratio). Kotak berwarna = daun / keputusan. Geser ke samping bila pohon lebar.</p>
                    </div>
                    <button type="button" class="btn-ghost btn-sm" @click="toggle()" x-text="fit ? 'Ukuran asli' : 'Tampilkan seluruh pohon'">Tampilkan seluruh pohon</button>
                </div>
                <div class="dt mt-4 overflow-x-auto pb-4" x-ref="box">
                    <ul class="mx-auto w-max origin-top" x-ref="tree" :style="fit && { zoom: scale }">@include('admin.tree._node', ['node' => $model->tree])</ul>
                </div>
            </div>
        </section>

        @endif
        </div>

        <aside class="space-y-4">
            @if ($canTrain)
                <form action="{{ route('admin.tree.train') }}" method="post" class="card space-y-3 p-5">
                    @csrf
                    <h2 class="font-display text-lg font-semibold">Latih ulang model</h2>
                    <p class="text-xs text-stone-500">{{ $datasetSize }} data latih: @foreach ($classCounts as $k => $v){{ $k }} {{ $v }}{{ $loop->last ? '' : ', ' }}@endforeach</p>
                    <div class="grid grid-cols-2 gap-2">
                        <div><label class="label text-xs" for="algorithm">Algoritma</label><select id="algorithm" name="algorithm" class="input px-2"><option>C4.5</option><option>ID3</option></select></div>
                        <div><label class="label text-xs" for="test_ratio">Data uji</label><select id="test_ratio" name="test_ratio" class="input px-2"><option value="0.2">20%</option><option value="0.25">25%</option><option value="0.3">30%</option></select></div>
                        <div><label class="label text-xs" for="seed">Seed acak</label><input id="seed" name="seed" type="number" value="42" class="input px-2"></div>
                        <div><label class="label text-xs" for="min_samples">Min. sampel/cabang</label><select id="min_samples" name="min_samples" class="input px-2"><option value="2">2 (pohon penuh)</option><option value="5">5</option><option value="10" selected>10</option><option value="15">15</option></select></div>
                    </div>
                    <button class="btn-primary w-full">Latih sekarang</button>
                    <a href="{{ route('admin.records.index') }}" class="block text-center text-xs font-semibold text-sea-700 hover:underline">Kelola data latih →</a>
                </form>
            @endif

            @if ($model)
                <form action="{{ route('admin.tree.simulate') }}" method="post" class="card space-y-3 p-5">
                    @csrf
                    <h2 class="font-display text-lg font-semibold">Uji coba prediksi</h2>
                    @foreach (\App\Support\Sailing::ATTRIBUTES as $key => $attr)
                        <div class="grid grid-cols-[110px_1fr] items-center gap-2">
                            <label class="text-xs font-semibold" for="sim_{{ $key }}">{{ $attr['label'] }}</label>
                            <select id="sim_{{ $key }}" name="{{ $key }}" class="input py-1.5">@foreach ($attr['values'] as $v)<option @selected(old($key) === $v)>{{ $v }}</option>@endforeach</select>
                        </div>
                    @endforeach
                    <button class="btn-sea w-full">Prediksi</button>
                    @if ($sim = session('simulation'))
                        <div class="rounded-xl bg-sand-50 p-3">
                            <x-decision :value="$sim['label']" size="lg"/>
                            <p class="mt-2 text-xs text-stone-600">Jalur:
                                @foreach ($sim['path'] as $step){{ \App\Support\Sailing::label($step['attribute']) }} = {{ $step['value'] }}{{ ! empty($step['fallback']) ? ' (cabang tidak ada, pakai mayoritas)' : '' }}{{ $loop->last ? '' : ' → ' }}@endforeach
                            </p>
                        </div>
                    @endif
                </form>
            @endif
        </aside>
    </div>

    @if ($model)
        @php($m = $model->metrics)
        <section class="card mt-6 p-5">
            @php($rc = $m['root_calculation'])
            <h2 class="font-display text-lg font-semibold">Perhitungan simpul akar</h2>
            <p class="mt-1 text-sm text-stone-600">
                Total data latih <strong>{{ $rc['total'] }}</strong>
                (@foreach ($rc['distribution'] as $k => $v){{ $k }} = {{ $v }}{{ $loop->last ? '' : ', ' }}@endforeach).
                Entropy(S) = <strong class="num">{{ number_format($rc['entropy'], 4) }}</strong>
            </p>
            <div class="mt-4 overflow-x-auto">
                <table class="table-base num">
                    <thead><tr><th>Atribut</th><th>Nilai</th><th class="text-right">Jumlah</th>@foreach (\App\Support\Sailing::DECISIONS as $d)<th class="text-right">{{ $d }}</th>@endforeach<th class="text-right">Entropy</th><th class="text-right">Gain</th><th class="text-right">Split Info</th><th class="text-right">Gain Ratio</th></tr></thead>
                    <tbody>
                        @php($bestAttr = $model->tree['attribute'] ?? null)
                        @foreach ($rc['attributes'] as $a)
                            @foreach ($a['values'] as $value => $info)
                                <tr @class(['bg-coral-50/60' => $a['attribute'] === $bestAttr])>
                                    @if ($loop->first)<td rowspan="{{ count($a['values']) }}" class="font-semibold">{{ \App\Support\Sailing::label($a['attribute']) }}@if ($a['attribute'] === $bestAttr)<span class="mt-1 block text-[10px] font-bold text-coral-600 uppercase">terpilih jadi akar</span>@endif</td>@endif
                                    <td>{{ $value }}</td>
                                    <td class="text-right">{{ $info['count'] }}</td>
                                    @foreach (\App\Support\Sailing::DECISIONS as $d)<td class="text-right">{{ $info['distribution'][$d] ?? 0 }}</td>@endforeach
                                    <td class="text-right">{{ number_format($info['entropy'], 4) }}</td>
                                    @if ($loop->first)
                                        <td rowspan="{{ count($a['values']) }}" class="text-right font-semibold">{{ number_format($a['gain'], 4) }}</td>
                                        <td rowspan="{{ count($a['values']) }}" class="text-right">{{ number_format($a['split_info'], 4) }}</td>
                                        <td rowspan="{{ count($a['values']) }}" class="text-right font-semibold">{{ number_format($a['gain_ratio'], 4) }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-xs leading-relaxed text-stone-500">
                Entropy(S) = −Σ pᵢ·log₂(pᵢ) · Gain(S,A) = Entropy(S) − Σ (|Sᵥ|/|S|)·Entropy(Sᵥ) ·
                SplitInfo(S,A) = −Σ (|Sᵥ|/|S|)·log₂(|Sᵥ|/|S|) · GainRatio = Gain / SplitInfo.
                {{ $model->algorithm === 'C4.5' ? 'C4.5 memilih gain ratio tertinggi di antara atribut dengan gain ≥ rata-rata.' : 'ID3 memilih gain tertinggi.' }}
            </p>
        </section>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            <section class="card p-5">
                <h2 class="font-display text-lg font-semibold">Confusion matrix (data uji)</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="num w-full text-center text-sm">
                        <thead><tr><th class="p-2 text-left text-xs text-stone-500">Aktual ↓ / Prediksi →</th>@foreach (\App\Support\Sailing::DECISIONS as $d)<th class="p-2 text-xs">{{ $d }}</th>@endforeach</tr></thead>
                        <tbody>
                            @foreach ($m['confusion_matrix'] as $actual => $row)
                                <tr>
                                    <th class="p-2 text-left text-xs">{{ $actual }}</th>
                                    @foreach ($row as $pred => $n)
                                        <td class="p-1"><div @class(['rounded-lg p-2 font-semibold', 'bg-sea-600 text-white' => $pred === $actual && $n, 'bg-coral-100 text-coral-700' => $pred !== $actual && $n, 'bg-sand-50 text-stone-400' => ! $n])>{{ $n }}</div></td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 overflow-x-auto"><table class="table-base num">
                    <thead><tr><th>Kelas</th><th class="text-right">Precision</th><th class="text-right">Recall</th><th class="text-right">F1</th><th class="text-right">Support</th></tr></thead>
                    <tbody>
                        @foreach ($m['per_class'] as $class => $s)
                            <tr><td><x-decision :value="$class"/></td><td class="text-right">{{ $s['precision'] }}%</td><td class="text-right">{{ $s['recall'] }}%</td><td class="text-right">{{ $s['f1'] }}%</td><td class="text-right">{{ $s['support'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table></div>
                <p class="mt-3 text-xs text-stone-500">Akurasi = {{ $m['correct'] }} benar / {{ $m['total'] }} data uji = {{ $m['accuracy'] }}%. Validasi silang {{ $m['cross_validation']['k'] }}-fold: {{ implode('%, ', $m['cross_validation']['scores']) }}%.</p>
            </section>

            <section class="card p-5">
                <h2 class="font-display text-lg font-semibold">Aturan IF–THEN ({{ count($model->rules) }})</h2>
                <ol class="mt-3 max-h-[28rem] space-y-2 overflow-y-auto pr-1 text-sm">
                    @foreach ($model->rules as $i => $rule)
                        <li class="rounded-lg bg-sand-50 p-2.5">
                            <span class="num mr-1 text-xs font-bold text-stone-400">R{{ $i + 1 }}</span>
                            <span class="font-semibold text-sea-700">IF</span>
                            @foreach ($rule['conditions'] as [$attr, $val]){{ \App\Support\Sailing::label($attr) }} = <strong>{{ $val }}</strong>{!! $loop->last ? '' : ' <span class="font-semibold text-sea-700">AND</span> ' !!}@endforeach
                            <span class="font-semibold text-sea-700">THEN</span> <x-decision :value="$rule['label']"/>
                            <span class="num text-xs text-stone-400">({{ $rule['samples'] }})</span>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>
    @endif

    <section class="card mt-6 overflow-x-auto p-5">
        <h2 class="font-display text-lg font-semibold">Riwayat pelatihan</h2>
        <table class="table-base num mt-2">
            <thead><tr><th>#</th><th>Algoritma</th><th>Waktu</th><th class="text-right">Latih/Uji</th><th class="text-right">Akurasi</th><th class="text-right">CV</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($history as $h)
                    <tr>
                        <td>{{ $h->id }}</td><td>{{ $h->algorithm }}</td><td>{{ $h->created_at->translatedFormat('d M Y H:i') }}</td>
                        <td class="text-right">{{ $h->training_size }}/{{ $h->testing_size }}</td>
                        <td class="text-right font-semibold">{{ $h->accuracy }}%</td>
                        <td class="text-right">{{ $h->metrics['cross_validation']['mean'] ?? '—' }}%</td>
                        <td>@if ($h->is_active)<span class="chip tone-layak">Aktif</span>@endif</td>
                        <td class="text-right"><a href="{{ route('admin.tree.show', $h) }}" class="text-xs font-semibold text-sea-700 hover:underline">Lihat</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-6 text-center text-stone-500">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-layouts.admin>
