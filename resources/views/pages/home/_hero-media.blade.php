{{-- Hero layar penuh: foto bergantian atau video pemandangan gili. --}}
<section class="relative isolate flex min-h-[86svh] items-end overflow-hidden bg-sea-950 text-white"
         x-data="heroSlider({ count: {{ $heroMedia->count() }} })">
    @foreach ($heroMedia as $i => $m)
        <div class="absolute inset-0 -z-20 transition-opacity duration-1000" :class="active === {{ $i }} ? 'opacity-100' : 'opacity-0'" @if ($i > 0) x-cloak @endif>
            @if ($m->isVideo())
                <video data-src="{{ $m->url() }}" @if ($m->posterUrl()) poster="{{ $m->posterUrl() }}" @endif
                       muted loop playsinline preload="none" aria-label="{{ $m->title }}"
                       class="h-full w-full object-cover" x-init="loadVideo($el)"></video>
            @else
                <img src="{{ $m->url() }}" alt="{{ $m->title }}" class="h-full w-full object-cover"
                     @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
            @endif
        </div>
    @endforeach
    {{-- gradasi agar tulisan tetap terbaca di atas foto apa pun --}}
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-sea-950/95 via-sea-950/50 to-sea-950/20"></div>

    <div class="container-x w-full pt-28 pb-12 sm:pb-16">
        <p class="inline-flex rounded-full bg-sea-950/50 px-3 py-1 text-xs font-bold tracking-[0.18em] text-white uppercase ring-1 ring-white/20 backdrop-blur">Dermaga Tawun · Sekotong, Lombok Barat</p>
        <h1 class="h-display mt-4 max-w-3xl text-[2.6rem] leading-[1.02] text-white drop-shadow-[0_2px_12px_rgba(0,0,0,0.35)] sm:text-6xl lg:text-7xl">
            Tiga gili, satu perahu, <span class="text-coral-400 italic">satu harga.</span>
        </h1>
        <p class="mt-5 max-w-xl text-lg leading-relaxed text-white drop-shadow-[0_1px_6px_rgba(0,0,0,0.4)]">
            Snorkeling di Gili Nanggu, Sudak, dan Kedis cukup <strong class="font-bold text-white">{{ rb(350000) }} per perahu</strong>, berempat atau dua belas orang.
        </p>

        <div class="mt-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <form action="{{ route('boats.index') }}" method="get" class="flex max-w-md flex-1 flex-col gap-2 rounded-2xl bg-white p-2 text-ink shadow-lg sm:flex-row sm:items-center">
                <label for="rombongan" class="flex flex-1 items-center gap-3 px-3 py-1">
                    <x-icon name="users" class="size-5 text-sea-600"/>
                    <span class="flex-1">
                        <span class="block text-[11px] font-bold tracking-wide text-stone-500 uppercase">Jumlah rombongan</span>
                        <input id="rombongan" name="rombongan" type="number" min="1" max="12" value="4" class="w-full border-0 p-0 text-base font-semibold focus:ring-0 focus:outline-none">
                    </span>
                </label>
                <button class="btn-primary py-3">Cari perahu <x-icon name="arrow" class="size-4"/></button>
            </form>

            <div class="flex items-center gap-3">
                <a href="{{ route('sea') }}" class="flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur hover:bg-white/15">
                    <x-icon name="wave" class="size-6 text-sea-200"/>
                    <span>
                        <span class="block text-[11px] font-bold tracking-wide text-sea-200 uppercase">Laut {{ ! $today || $today->date->isToday() ? 'hari ini' : ($today->date->isTomorrow() ? 'besok' : $today->date->translatedFormat('d M')) }}</span>
                        <x-decision :value="$today?->decision()" class="mt-0.5"/>
                    </span>
                </a>
            </div>
        </div>

        @if ($heroMedia->count() > 1)
            <div class="mt-8 flex items-center gap-3">
                @foreach ($heroMedia as $i => $m)
                    <button type="button" @click="go({{ $i }})" class="h-1.5 rounded-full transition-all" :class="active === {{ $i }} ? 'w-8 bg-white' : 'w-4 bg-white/40 hover:bg-white/70'" aria-label="Tampilkan {{ $m->title }}"></button>
                @endforeach
                <span class="ml-2 text-xs text-sea-100" x-text="@js($heroMedia->pluck('title')->values())[active]"></span>
            </div>
        @else
            <p class="mt-8 text-xs text-sea-100">{{ $heroMedia->first()->title }}{{ $heroMedia->first()->location ? ' · '.$heroMedia->first()->location : '' }}</p>
        @endif
    </div>
</section>
