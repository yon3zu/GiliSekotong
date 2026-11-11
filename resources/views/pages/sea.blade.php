<x-layouts.app title="Kondisi Laut & Cuaca" description="Cuaca BMKG Dusun Tawun Sekotong Barat, peringatan dini NTB, gempa dirasakan di sekitar Lombok, dan kelayakan berlayar ke tiga gili.">
    @php($todayCondition = $conditions[today()->toDateString()] ?? $conditions->first())
    @php($activeAlerts = $alerts->filter->isActive())

    {{-- HERO: cuaca saat ini + status berlayar --}}
    <section class="border-b border-sand-200 bg-sand-100">
        <div class="container-x grid gap-8 py-10 sm:py-14 lg:grid-cols-[1.1fr_1fr] lg:items-center">
            <div>
                <p class="eyebrow">{{ config('bmkg.location.name') }} · Sumber BMKG</p>
                <h1 class="h-display mt-3 text-4xl sm:text-5xl">Kondisi Laut &amp; Cuaca</h1>
                <p class="mt-4 max-w-xl text-stone-700">
                    Cuaca, peringatan dini, dan gempa dari BMKG untuk sekitar Dermaga Tawun, lengkap dengan status kelayakan berlayar ke Gili Nanggu, Sudak, dan Kedis.
                </p>
                <div class="mt-5 flex flex-wrap gap-2 text-sm">
                    <a href="#prakiraan" class="chip bg-white px-3 py-1.5 ring-1 ring-sand-200 hover:ring-sea-300">Prakiraan per 3 jam</a>
                    <a href="#peringatan" class="chip bg-white px-3 py-1.5 ring-1 ring-sand-200 hover:ring-sea-300">Peringatan dini NTB</a>
                    <a href="#gempa" class="chip bg-white px-3 py-1.5 ring-1 ring-sand-200 hover:ring-sea-300">Gempa Lombok</a>
                    <a href="#keputusan" class="chip bg-white px-3 py-1.5 ring-1 ring-sand-200 hover:ring-sea-300">Jadwal berlayar 14 hari</a>
                </div>
                @if ($updated)<p class="mt-4 text-xs text-stone-500">Data BMKG diperbarui {{ $updated->diffForHumans() }}.</p>@endif
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                {{-- Cuaca sekarang --}}
                <div class="card p-5 sm:col-span-2">
                    @if ($current)
                        <div class="flex items-center gap-4">
                            <x-weather-icon :code="$current->weather_code" class="size-14 shrink-0 text-sea-700"/>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-stone-500">Cuaca {{ $current->local_datetime->isToday() ? 'pukul' : $current->local_datetime->translatedFormat('l') }} {{ $current->local_datetime->format('H:i') }} WITA</p>
                                <p class="font-display text-2xl font-semibold">{{ $current->weather_desc }}</p>
                            </div>
                            <p class="num font-display text-4xl font-semibold text-sea-950">{{ round($current->temperature) }}°</p>
                        </div>
                        <dl class="mt-4 grid grid-cols-3 gap-2 border-t border-sand-100 pt-4 text-center text-xs">
                            <div><dt class="text-stone-500">Angin</dt><dd class="num font-semibold">{{ $current->windKnot() }} kn · {{ $current->windDirLabel() }}</dd></div>
                            <div><dt class="text-stone-500">Kelembapan</dt><dd class="num font-semibold">{{ $current->humidity }}%</dd></div>
                            <div><dt class="text-stone-500">Tutupan awan</dt><dd class="num font-semibold">{{ $current->cloud_cover }}%</dd></div>
                        </dl>
                    @else
                        <p class="text-sm text-stone-600">Data cuaca BMKG belum tersedia. Akan terisi otomatis pada sinkronisasi berikutnya.</p>
                    @endif
                </div>
                {{-- Status berlayar terdekat --}}
                <a href="#keputusan" class="card p-4 hover:border-sea-300">
                    <p class="text-xs font-semibold text-stone-500">Berlayar {{ $todayCondition ? ($todayCondition->date->isToday() ? 'hari ini' : ($todayCondition->date->isTomorrow() ? 'besok' : $todayCondition->date->translatedFormat('d M'))) : 'hari ini' }}</p>
                    <x-decision :value="$todayCondition?->decision()" size="lg" class="mt-2"/>
                </a>
                <div class="grid grid-rows-2 gap-3">
                    <a href="#peringatan" @class(['card flex items-center gap-2 px-4 py-2 text-xs font-semibold', 'border-red-200 bg-red-50 text-red-800' => $activeAlerts->isNotEmpty()])>
                        <x-icon name="alert" class="size-4 shrink-0"/> {{ $activeAlerts->isNotEmpty() ? $activeAlerts->count().' peringatan NTB aktif' : 'Tidak ada peringatan NTB' }}
                    </a>
                    <a href="#gempa" @class(['card flex items-center gap-2 px-4 py-2 text-xs font-semibold', 'border-amber-200 bg-amber-50 text-amber-800' => $quakes->isNotEmpty()])>
                        <x-icon name="quake" class="size-4 shrink-0"/> {{ $quakes->isNotEmpty() ? $quakes->count().' gempa di sekitar Lombok' : 'Tidak ada gempa di Lombok' }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="container-x space-y-14 py-10">
        {{-- Prakiraan cuaca --}}
        <section id="prakiraan" class="card scroll-mt-24 p-5 sm:p-7">
            <h2 class="h-display text-2xl sm:text-3xl">Prakiraan cuaca per 3 jam</h2>
            <p class="text-sm text-stone-600">{{ config('bmkg.location.name') }}, Kec. Sekotong, Lombok Barat · 3 hari ke depan</p>
            <div class="mt-5">@include('bmkg._forecast', ['days' => $forecast])</div>
        </section>

        {{-- Peringatan dini & gempa --}}
        <div class="grid gap-8 lg:grid-cols-2">
            <section id="peringatan" class="scroll-mt-24">
                <h2 class="h-display text-2xl">Peringatan dini cuaca NTB</h2>
                <p class="mt-1 text-sm text-stone-600">Nowcast BMKG yang menyebut Nusa Tenggara Barat. Peringatan di kecamatan sekitar rute (Sekotong, Lembar, Gerung, dst.) menutup pelayaran selama jam berlakunya.</p>
                <div class="mt-4 space-y-3">@include('bmkg._alerts', ['alerts' => $alerts])</div>
            </section>
            <section id="gempa" class="scroll-mt-24">
                <h2 class="h-display text-2xl">Gempa dirasakan · Lombok &amp; sekitarnya</h2>
                <p class="mt-1 text-sm text-stone-600">Dari 15 gempa dirasakan terbaru BMKG, hanya yang berpusat ≤ {{ config('bmkg.quake_radius_km') }} km dari Tawun atau menyebut Lombok/NTB. Gempa ≥ M{{ config('bmkg.quake_caution_magnitude') }} dalam 24 jam terakhir membuat status minimal Waspada.</p>
                <div class="mt-4 space-y-3">@include('bmkg._quakes', ['quakes' => $quakes])</div>
            </section>
        </div>

        {{-- Keputusan per hari --}}
        <section id="keputusan" class="scroll-mt-24">
            <h2 class="h-display text-2xl sm:text-3xl">Jadwal berlayar 14 hari</h2>
            <p class="mt-1 max-w-3xl text-sm text-stone-600">
                Prakiraan BMKG diolah <strong>pohon keputusan C4.5</strong>, lalu diperiksa terhadap peringatan dini dan gempa di atas.
                Nakhoda tetap memegang keputusan akhir di dermaga.
            </p>
            <div class="mt-3 flex flex-wrap gap-3 text-sm">
                @foreach (\App\Support\Sailing::DECISIONS as $d)
                    <span class="flex items-center gap-2"><x-decision :value="$d"/> <span class="text-stone-600">{{ \Illuminate\Support\Str::before(\App\Support\Sailing::advice($d), '.') }}.</span></span>
                @endforeach
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @for ($i = 0; $i < 14; $i++)
                    @php($date = today()->addDays($i))
                    @php($c = $conditions[$date->toDateString()] ?? null)
                    <article @class(['card p-4', 'ring-2 ring-coral-400' => $i === 0])>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-semibold text-stone-500">{{ $i === 0 ? 'Hari ini' : ($i === 1 ? 'Besok' : $date->translatedFormat('l')) }}</p>
                                <p class="font-display text-lg font-semibold">{{ $date->translatedFormat('d M') }}</p>
                            </div>
                            <x-decision :value="$c?->decision()"/>
                        </div>
                        @if ($c)
                            <dl class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                <div class="flex items-center gap-1.5"><x-icon name="sun" class="size-4 text-sea-600"/>{{ $c->cuaca }}</div>
                                <div class="flex items-center gap-1.5"><x-icon name="wind" class="size-4 text-sea-600"/><span class="num">{{ $c->wind_knot + 0 }} kn</span></div>
                                <div class="flex items-center gap-1.5"><x-icon name="wave" class="size-4 text-sea-600"/><span class="num">{{ $c->source === 'bmkg' ? '±' : '' }}{{ $c->wave_m + 0 }} m</span></div>
                                <div class="flex items-center gap-1.5"><x-icon name="eye" class="size-4 text-sea-600"/>{{ $c->jarak_pandang }}</div>
                            </dl>
                            <p class="mt-3 border-t border-sand-100 pt-2 text-[11px] text-stone-500">
                                {{ $c->source === 'bmkg' ? 'Prakiraan BMKG' : 'Catatan petugas dermaga' }} · pohon keputusan: {{ $c->prediction ?? '—' }}
                            </p>
                            @include('bmkg._reasons', ['condition' => $c])
                            @if ($c->final_decision && $c->note)
                                <p class="mt-2 text-[11px] text-stone-600">Nakhoda: {{ $c->note }}</p>
                            @endif
                        @else
                            <p class="mt-4 text-xs text-stone-500">{{ $i === 0 ? 'Jam operasional perahu hari ini sudah lewat atau belum ada data.' : 'Di luar jangkauan prakiraan BMKG (3 hari). Diperbarui mendekati tanggal.' }}</p>
                        @endif
                    </article>
                @endfor
            </div>
        </section>

        <section class="grid gap-8 rounded-3xl border border-sand-200 bg-white p-6 sm:p-10 lg:grid-cols-2">
            <div>
                <h2 class="h-display text-2xl sm:text-3xl">Bagaimana keputusan dibuat?</h2>
                <ol class="mt-4 list-inside list-decimal space-y-2 leading-relaxed text-stone-700">
                    <li>Prakiraan BMKG pada jam operasional perahu dirangkum menjadi kondisi terburuk: cuaca, kecepatan angin, dan jarak pandang. Tinggi gelombang diperkirakan dari kecepatan angin (skala Beaufort) karena API prakiraan cuaca tidak memuatnya.</li>
                    <li>Pohon keputusan, yang belajar dari catatan pelayaran sebelumnya, memprediksi Layak, Waspada, atau Tidak Layak.</li>
                    <li>Aturan keselamatan BMKG hanya bisa <em>memperberat</em> hasil: peringatan dini di sekitar rute atau gempa kuat di dekat Lombok.</li>
                    <li>Petugas dermaga dapat mengganti data dengan pengamatan langsung, dan nakhoda menetapkan keputusan akhir.</li>
                </ol>
            </div>
            <dl class="grid gap-3 text-sm">
                @foreach (\App\Support\Sailing::ATTRIBUTES as $attr)
                    <div class="rounded-xl bg-sand-50 p-3">
                        <dt class="font-semibold">{{ $attr['label'] }}</dt>
                        <dd class="text-stone-600">{{ $attr['hint'] ?? implode(' · ', $attr['values']) }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <p class="text-xs text-stone-500">Sumber data: Badan Meteorologi, Klimatologi, dan Geofisika (BMKG) — prakiraan cuaca, peringatan dini cuaca, dan gempa bumi dirasakan.</p>
    </div>
</x-layouts.app>
