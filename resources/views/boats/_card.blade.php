<a href="{{ route('boats.show', $boat) }}" class="group card flex flex-col overflow-hidden transition hover:-translate-y-1 hover:shadow-lg">
    <div class="relative aspect-[16/10] overflow-hidden">
        <x-boat-photo :boat="$boat" class="h-full w-full transition duration-500 group-hover:scale-105"/>
        <span class="absolute top-3 left-3 chip bg-white/95 text-sea-900 shadow-sm"><x-icon name="users" class="size-3.5"/> Maks. {{ $boat->capacity }} orang</span>
    </div>
    <div class="flex flex-1 flex-col p-5">
        <p class="text-xs font-semibold text-stone-500">{{ $boat->type }} · {{ $boat->length_m }} m</p>
        <h3 class="mt-1 font-display text-xl font-semibold text-sea-950">{{ $boat->name }}</h3>
        @if ($boat->visible_reviews_count ?? 0)
            <p class="mt-1 flex items-center gap-1.5 text-xs text-stone-600"><x-stars :value="$boat->visible_reviews_avg_rating" class="size-3.5"/><span class="num font-semibold">{{ number_format($boat->visible_reviews_avg_rating, 1, ',', '') }}</span> ({{ $boat->visible_reviews_count }} ulasan)</p>
        @endif
        <p class="mt-2 line-clamp-2 text-sm text-stone-600">{{ $boat->description }}</p>
        <div class="mt-auto flex items-end justify-between pt-5">
            <p><span class="block text-[11px] text-stone-500">Trip 3 gili / mancing</span><span class="num font-display text-xl font-semibold text-coral-600">{{ rupiah($boat->trip_price) }}</span><span class="text-xs text-stone-500"> /perahu</span></p>
            <span class="inline-flex size-9 items-center justify-center rounded-full bg-sea-800 text-white transition group-hover:bg-coral-500"><x-icon name="arrow" class="size-4"/></span>
        </div>
    </div>
</a>
