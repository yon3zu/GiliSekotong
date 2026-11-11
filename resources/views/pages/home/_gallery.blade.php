{{-- Galeri keindahan 3 gili. Sebelum ada foto asli, tampil ilustrasi sebagai pengganti. --}}
<section class="container-x py-16 lg:py-24" x-data="lightbox(@js($galleryData))">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="max-w-2xl">
            <p class="eyebrow">Keindahan 3 Gili Sekotong</p>
            <h2 class="h-display mt-3 text-3xl sm:text-4xl">Air sebening kaca, pasir putih, dan pulau yang masih sepi.</h2>
        </div>
        <a href="{{ route('destinations') }}" class="inline-flex items-center gap-2 font-semibold text-sea-700 hover:text-coral-600">Kenali tiap gili <x-icon name="arrow" class="size-4"/></a>
    </div>

    @if ($gallery->isNotEmpty())
        @php
            // Tata letak mosaik tanpa celah: 5+ foto → foto pertama 2×2, foto terakhir dilebarkan menutup baris terakhir.
            // 1–4 foto → semua sama besar. Kelas ditulis utuh agar terbaca Tailwind.
            $n = $gallery->count();
            $mdCols = [1 => 'md:grid-cols-1', 2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3'][$n] ?? 'md:grid-cols-4';
            $mdSpan = [2 => 'md:col-span-2', 3 => 'md:col-span-3', 4 => 'md:col-span-4'];
            $tileClass = function (int $i) use ($n, $mdSpan) {
                if ($n < 5) {
                    return 'col-span-2 row-span-2 md:col-span-1';
                }
                if ($i === 0) {
                    return 'col-span-2 row-span-2';
                }
                $classes = [];
                $rest = $n - 1;                      // foto selain yang pertama
                if ($i === $n - 1 && $rest % 2 === 1) {
                    $classes[] = 'col-span-2';     // HP: 2 kolom
                }
                $lastRow = ($n - 5) % 4;              // jumlah foto di baris terakhir (layar lebar)
                if ($i === $n - 1 && $lastRow > 0) {
                    $classes[] = $mdSpan[4 - $lastRow + 1];
                }

                return implode(' ', $classes);
            };
        @endphp
        <div class="mt-8 grid auto-rows-[160px] grid-cols-2 gap-3 sm:auto-rows-[200px] {{ $mdCols }}">
            @foreach ($gallery as $i => $m)
                <button type="button" @click="open({{ $i }})"
                        class="group relative overflow-hidden rounded-2xl bg-sea-900 text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-coral-500 {{ $tileClass($i) }}">
                    @if ($m->stillUrl())
                        <img src="{{ $m->stillUrl() }}" alt="{{ $m->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    @else
                        <video src="{{ $m->url() }}#t=1" muted playsinline preload="metadata" class="h-full w-full object-cover" aria-label="{{ $m->title }}"></video>
                    @endif
                    <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-3 text-sm font-semibold text-white">
                        {{ $m->title }}@if ($m->location)<span class="block text-xs font-normal text-white/80">{{ $m->location }}</span>@endif
                    </span>
                    @if ($m->isVideo())
                        <span class="absolute top-3 left-3 flex size-10 items-center justify-center rounded-full bg-white/90 text-sea-900 shadow"><svg viewBox="0 0 24 24" class="ml-0.5 size-5" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg></span>
                    @endif
                </button>
            @endforeach
        </div>
    @else
        <div class="mt-8 grid gap-3 md:grid-cols-3">
            @foreach ([['Gili Nanggu', 1, '#246b4a', 'Snorkeling di karang dangkal'], ['Gili Sudak', 0.85, '#1f7350', 'Makan siang di tepi pantai'], ['Gili Kedis', 0.45, '#2f8a5f', 'Pulau mungil untuk berfoto']] as [$name, $size, $tone, $line])
                <figure class="overflow-hidden rounded-2xl border border-sand-200 bg-white">
                    <x-island :size="$size" :tone="$tone" class="w-full"/>
                    <figcaption class="p-4"><p class="font-display text-lg font-semibold">{{ $name }}</p><p class="text-sm text-stone-600">{{ $line }}</p></figcaption>
                </figure>
            @endforeach
        </div>
        @auth
            @if (auth()->user()->isStaff())
                <p class="mt-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-900 ring-1 ring-amber-200">
                    Ilustrasi ini tampil sementara. Unggah foto atau video asli di <a href="{{ route('admin.media.index') }}" class="font-semibold underline">Admin → Galeri Beranda</a>.
                </p>
            @endif
        @endauth
    @endif

    {{-- Lightbox --}}
    <div x-show="index !== null" x-cloak x-transition.opacity @keydown.escape.window="close()" @keydown.arrow-right.window="next()" @keydown.arrow-left.window="prev()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4" role="dialog" aria-modal="true" aria-label="Galeri foto" @click.self="close()">
        <template x-if="current">
            <figure class="relative w-full max-w-5xl">
                <template x-if="current.video"><video :src="current.src" :poster="current.poster" controls autoplay playsinline class="max-h-[80vh] w-full rounded-xl bg-black"></video></template>
                <template x-if="!current.video"><img :src="current.src" :alt="current.title" class="max-h-[80vh] w-full rounded-xl object-contain"></template>
                <figcaption class="mt-3 text-center text-sm text-white"><span x-text="current.title"></span><span class="text-white/60" x-show="current.location" x-text="' · ' + current.location"></span></figcaption>
            </figure>
        </template>
        <button type="button" @click="close()" class="absolute top-4 right-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" aria-label="Tutup"><x-icon name="x" class="size-6"/></button>
        <button type="button" @click="prev()" class="absolute left-3 rounded-full bg-white/10 p-3 text-white hover:bg-white/20" aria-label="Sebelumnya" x-show="items.length > 1"><x-icon name="back" class="size-5"/></button>
        <button type="button" @click="next()" class="absolute right-3 rounded-full bg-white/10 p-3 text-white hover:bg-white/20" aria-label="Berikutnya" x-show="items.length > 1"><x-icon name="arrow" class="size-5"/></button>
    </div>
</section>
