{{-- Gempa dirasakan di Lombok & sekitarnya dari 15 kejadian terbaru BMKG. $quakes (sudah difilter) --}}
@forelse ($quakes as $q)
    @php($recent = $q->occurred_at->gt(now()->subDay()))
    <article @class(['rounded-xl border p-4', 'border-amber-200 bg-amber-50' => $recent, 'border-sand-200 bg-white' => ! $recent])>
        <div class="flex items-start gap-3">
            <span class="num flex size-12 shrink-0 flex-col items-center justify-center rounded-xl bg-sea-900 text-white">
                <span class="text-[9px] leading-none text-sea-200">M</span><span class="font-display text-lg leading-none font-semibold">{{ number_format($q->magnitude, 1) }}</span>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold">{{ $q->region }}</p>
                <p class="num text-xs text-stone-600">{{ $q->occurred_at->translatedFormat('l, d M Y H:i') }} WITA · kedalaman {{ $q->depth_km ?? '—' }} km · {{ $q->distance_km }} km dari Tawun</p>
                @if ($q->felt)<p class="mt-1 text-xs text-stone-600">Dirasakan (MMI): {{ $q->felt }}</p>@endif
            </div>
        </div>
    </article>
@empty
    <p class="flex items-center gap-2 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-900 ring-1 ring-emerald-200">
        <x-icon name="check" class="size-4 shrink-0"/> Tidak ada gempa dirasakan di Lombok, NTB, dan sekitarnya dalam 15 kejadian terbaru BMKG.
    </p>
@endforelse
