{{-- Prakiraan cuaca BMKG per 3 jam. $days: koleksi BmkgForecast dikelompokkan per tanggal --}}
@php([$from, $to] = config('bmkg.sailing_hours'))
<div class="space-y-5">
    @forelse ($days as $date => $slots)
        @php($d = \Illuminate\Support\Carbon::parse($date))
        <div>
            <p class="mb-2 text-sm font-semibold text-sea-950">
                {{ $d->isToday() ? 'Hari ini' : ($d->isTomorrow() ? 'Besok' : $d->translatedFormat('l')) }},
                <span class="font-normal text-stone-500">{{ $d->translatedFormat('d F') }}</span>
            </p>
            <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
                @foreach ($slots as $s)
                    @php($inHours = $s->local_datetime->hour >= $from && $s->local_datetime->hour <= $to)
                    <div @class(['w-24 shrink-0 rounded-xl p-3 text-center', 'bg-sea-50 ring-1 ring-sea-200' => $inHours, 'bg-sand-50' => ! $inHours])
                         title="{{ $inHours ? 'Jam operasional perahu' : '' }}">
                        <p class="num text-xs font-bold text-stone-500">{{ $s->local_datetime->format('H:i') }}</p>
                        <x-weather-icon :code="$s->weather_code" class="mx-auto mt-1.5 size-7 text-sea-700"/>
                        <p class="mt-1 line-clamp-2 min-h-8 text-[11px] leading-tight font-semibold">{{ $s->weather_desc }}</p>
                        <p class="num mt-1 font-display text-lg font-semibold">{{ round($s->temperature) }}°</p>
                        <p class="num text-[11px] text-stone-600" title="{{ $s->wind_kmh }} km/jam dari {{ $s->windDirLabel() }}">
                            <x-icon name="wind" class="inline size-3"/> {{ $s->windKnot() }} kn {{ $s->wind_dir }}
                        </p>
                        <p class="num text-[11px] text-stone-500">RH {{ $s->humidity }}%</p>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <p class="rounded-xl bg-sand-50 p-4 text-sm text-stone-600">Prakiraan BMKG belum tersedia. Data akan terisi otomatis pada sinkronisasi berikutnya.</p>
    @endforelse
    <p class="text-xs text-stone-500"><span class="inline-block size-2.5 rounded-sm bg-sea-100 ring-1 ring-sea-200 align-middle"></span> jam operasional perahu ({{ sprintf('%02d.00–%02d.00', $from, $to) }} WITA). Kecepatan angin dalam knot.</p>
</div>
