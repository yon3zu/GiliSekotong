<x-layouts.app>
    @if ($alerts->isNotEmpty() || $recentQuake)
        <div class="border-b border-amber-200 bg-amber-50 text-amber-900">
            <a href="{{ route('sea') }}" class="container-x flex items-start gap-3 py-3 text-sm">
                <x-icon :name="$alerts->isNotEmpty() ? 'alert' : 'quake'" class="mt-0.5 size-4 shrink-0"/>
                <span class="flex-1">
                    @if ($alerts->isNotEmpty())
                        <strong>Peringatan dini BMKG:</strong> {{ $alerts->first()->title }}@if ($alerts->first()->expires_at), hingga {{ $alerts->first()->expires_at->format('H:i') }} WITA @endif.
                    @endif
                    @if ($recentQuake)
                        <strong>Gempa M{{ $recentQuake->magnitude }}</strong> {{ $recentQuake->distance_km }} km dari Tawun, {{ $recentQuake->occurred_at->diffForHumans() }}.
                    @endif
                    Lihat dampaknya ke jadwal pelayaran →
                </span>
            </a>
        </div>
    @endif

    {{-- HERO --}}
    @if ($heroMedia->isNotEmpty())
        @include('pages.home._hero-media')
    @else
        <section class="relative overflow-hidden">
            <div class="container-x grid items-center gap-10 pt-10 pb-16 md:pt-16 lg:grid-cols-[1.05fr_1fr] lg:gap-14 lg:pb-24">
                <div>
                    <p class="eyebrow">Dermaga Tawun · Sekotong, Lombok Barat</p>
                    <h1 class="h-display mt-4 text-[2.6rem] leading-[1.02] sm:text-6xl">
                        Tiga gili,<br>satu perahu,<br><span class="text-coral-600 italic">satu harga.</span>
                    </h1>
                    <p class="mt-6 max-w-md text-lg leading-relaxed text-stone-700">
                        Snorkeling di Gili Nanggu, Sudak, dan Kedis cukup <strong class="font-bold text-sea-900">{{ rb(350000) }} per perahu</strong>.
                        Berangkat berempat atau dua belas orang, hitungannya tetap sama.
                    </p>
    
                    <form action="{{ route('boats.index') }}" method="get" class="mt-8 flex max-w-md flex-col gap-2 rounded-2xl border border-sand-200 bg-white p-2 shadow-sm sm:flex-row sm:items-center">
                        <label for="rombongan" class="flex flex-1 items-center gap-3 px-3 py-1">
                            <x-icon name="users" class="size-5 text-sea-600"/>
                            <span class="flex-1">
                                <span class="block text-[11px] font-bold tracking-wide text-stone-500 uppercase">Jumlah rombongan</span>
                                <input id="rombongan" name="rombongan" type="number" min="1" max="12" value="4" class="w-full border-0 p-0 text-base font-semibold focus:ring-0 focus:outline-none">
                            </span>
                        </label>
                        <button class="btn-primary py-3">Cari perahu <x-icon name="arrow" class="size-4"/></button>
                    </form>
    
                    <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-stone-600">
                        <span class="inline-flex items-center gap-1.5"><x-icon name="shield" class="size-4 text-sea-600"/>Nakhoda warga lokal</span>
                        <span class="inline-flex items-center gap-1.5"><x-icon name="boat" class="size-4 text-sea-600"/>Bisa sewa untuk mancing</span>
                        <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="size-4 text-sea-600"/>± 6 jam di laut</span>
                    </div>
                </div>
    
                <div class="relative">
                    <x-hero-art class="w-full drop-shadow-xl"/>
                    <a href="{{ route('sea') }}" class="absolute -bottom-5 left-4 flex items-center gap-3 rounded-2xl border border-sand-200 bg-white px-4 py-3 shadow-lg sm:left-8">
                        <x-icon name="wave" class="size-6 text-sea-600"/>
                        <span>
                            <span class="block text-[11px] font-bold tracking-wide text-stone-500 uppercase">
                                Laut {{ ! $today || $today->date->isToday() ? 'hari ini' : ($today->date->isTomorrow() ? 'besok' : $today->date->translatedFormat('d M')) }}
                            </span>
                            <x-decision :value="$today?->decision()" class="mt-0.5"/>
                        </span>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- GALERI --}}
    @include('pages.home._gallery')

    {{-- HARGA --}}
    <section class="bg-sea-900 text-white">
        <div class="wave-edge h-3 rotate-180"></div>
        <div class="container-x py-16 lg:py-20">
            <div class="max-w-2xl">
                <p class="eyebrow text-coral-400">Harga terbuka</p>
                <h2 class="h-display mt-3 text-3xl text-white sm:text-4xl">Hitung sendiri, tanpa biaya tersembunyi.</h2>
            </div>
            <div class="mt-10 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10">
                    <x-icon name="boat" class="size-7 text-coral-400"/>
                    <p class="mt-5 text-sm text-sea-200">Sewa perahu</p>
                    <p class="mt-1 font-display text-4xl font-semibold">{{ rb(350000) }}<span class="text-base font-normal text-sea-200"> /perahu</span></p>
                    <p class="mt-3 text-sm leading-relaxed text-sea-200">Sudah termasuk bensin dan nakhoda, untuk rombongan sampai {{ \App\Models\Boat::MAX_CAPACITY }} orang. Pilih trip 3 gili atau sewa untuk mancing, harganya sama.</p>
                </div>
                <div class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10">
                    <x-icon name="food" class="size-7 text-coral-400"/>
                    <p class="mt-5 text-sm text-sea-200">Paket makan · Kedai Bintang</p>
                    <p class="mt-1 font-display text-4xl font-semibold">{{ rb($meals->min('price') ?? 0) }}<span class="text-base font-normal text-sea-200"> /paket</span></p>
                    <ul class="mt-3 space-y-1 text-sm text-sea-200">
                        @foreach ($meals as $meal)
                            <li class="flex justify-between gap-3 border-b border-white/10 pb-1"><span>{{ $meal->name }}</span><span class="num font-semibold text-white">{{ rb($meal->price) }}</span></li>
                        @endforeach
                    </ul>
                </div>
                <div class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10">
                    <x-icon name="snorkel" class="size-7 text-coral-400"/>
                    <p class="mt-5 text-sm text-sea-200">Sewa alat</p>
                    <p class="mt-1 font-display text-4xl font-semibold">{{ rb($rentals->min('price') ?? 0) }}<span class="text-base font-normal text-sea-200"> /pcs</span></p>
                    <ul class="mt-3 space-y-1 text-sm text-sea-200">
                        @foreach ($rentals as $item)
                            <li class="flex justify-between gap-3 border-b border-white/10 pb-1"><span>{{ $item->name }}</span><span class="num font-semibold text-white">{{ rb($item->price) }}</span></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @php
                $exampleMeal = $meals->firstWhere('code', 'C') ?? $meals->first();
                $examplePax = 8;
                $exampleRental = $rentals->take(2)->sum('price') * $examplePax;
                $example = 350000 + 3 * ($exampleMeal?->price ?? 0) + $exampleRental;
            @endphp
            <div class="mt-8 rounded-2xl bg-coral-500/15 p-5 text-sm ring-1 ring-coral-400/30 sm:flex sm:items-center sm:justify-between sm:gap-6">
                <p><span class="font-semibold">Contoh:</span> {{ $examplePax }} orang + {{ $exampleMeal?->name }} ×3 + {{ $rentals->take(2)->pluck('name')->map(fn ($n) => strtolower($n))->join(' & ') }} untuk semua =
                    {{ rb(350000) }} + {{ rb(3 * ($exampleMeal?->price ?? 0)) }} + {{ rb($exampleRental) }} = <strong class="num">{{ rupiah($example) }}</strong></p>
                <p class="mt-1 shrink-0 text-sea-200 sm:mt-0">≈ {{ rupiah(ceil($example / $examplePax)) }} per orang</p>
            </div>
        </div>
    </section>

    {{-- RUTE --}}
    <section class="container-x py-16 lg:py-24">
        <div class="grid gap-10 lg:grid-cols-[1fr_1.4fr]">
            <div>
                <p class="eyebrow">Rute satu hari</p>
                <h2 class="h-display mt-3 text-3xl sm:text-4xl">Dari dermaga, singgah tiga pulau, pulang sebelum senja.</h2>
                <p class="mt-4 leading-relaxed text-stone-700">Urutan bisa diubah nakhoda menyesuaikan arus dan pasang surut. Yang pasti, Anda tidak perlu terburu-buru.</p>
                <a href="{{ route('destinations') }}" class="mt-6 inline-flex items-center gap-2 font-semibold text-sea-700 hover:text-coral-600">Kenali tiap gili <x-icon name="arrow" class="size-4"/></a>
            </div>
            <ol class="relative space-y-6 border-l-2 border-dashed border-sea-200 pl-8">
                @foreach ([
                    ['08.30', 'Dermaga Tawun', 'Briefing keselamatan dan pembagian alat snorkeling yang disewa.'],
                    ['09.00', 'Gili Nanggu', 'Snorkeling di sisi timur yang tenang, banyak ikan badut dan karang meja.'],
                    ['11.00', 'Gili Sudak', 'Makan siang di gazebo tepi pantai. Paket makan diantar ke sini.'],
                    ['13.00', 'Gili Kedis', 'Pulau mungil yang bisa dikelilingi 10 menit jalan kaki. Spot foto terbaik.'],
                    ['14.30', 'Kembali ke Tawun', 'Bilas di dermaga, perahu siap untuk rombongan berikutnya.'],
                ] as [$time, $place, $text])
                    <li class="relative">
                        <span class="absolute top-1 -left-[2.6rem] flex size-5 items-center justify-center rounded-full border-2 border-sea-500 bg-sand-50"><span class="size-1.5 rounded-full bg-coral-500"></span></span>
                        <p class="num text-xs font-bold tracking-wide text-coral-600">{{ $time }}</p>
                        <p class="font-display text-xl font-semibold text-sea-950">{{ $place }}</p>
                        <p class="mt-1 text-sm text-stone-600">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- PERAHU --}}
    <section class="bg-sand-100 py-16 lg:py-24">
        <div class="container-x">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Armada</p>
                    <h2 class="h-display mt-3 text-3xl sm:text-4xl">Pilih perahu sesuai rombongan.</h2>
                </div>
                <a href="{{ route('boats.index') }}" class="btn-ghost">Semua perahu <x-icon name="arrow" class="size-4"/></a>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($boats as $boat)
                    @include('boats._card', ['boat' => $boat])
                @endforeach
            </div>
        </div>
    </section>

    {{-- KESELAMATAN / DECISION TREE --}}
    <section class="container-x py-16 lg:py-24">
        <div class="grid items-center gap-10 rounded-3xl border border-sand-200 bg-white p-6 sm:p-10 lg:grid-cols-2">
            <div>
                <p class="eyebrow">Keselamatan dulu</p>
                <h2 class="h-display mt-3 text-3xl sm:text-4xl">Berangkat atau tunda? Kami tidak menebak.</h2>
                <p class="mt-4 leading-relaxed text-stone-700">
                    Prakiraan cuaca <strong>BMKG</strong> untuk Dusun Tawun diolah <strong>model pohon keputusan</strong> yang belajar dari
                    ratusan catatan pelayaran sebelumnya. Hasilnya diperiksa lagi terhadap peringatan dini cuaca NTB dan gempa di sekitar Lombok,
                    lalu dikonfirmasi nakhoda.
                </p>
                <p class="mt-3 leading-relaxed text-stone-700">Jika laut dinilai tidak layak, tanggal tersebut ditutup dan pesanan dijadwalkan ulang tanpa biaya.</p>
                <a href="{{ route('sea') }}" class="btn-sea mt-6">Lihat kondisi laut &amp; cuaca</a>
            </div>
            <div class="grid grid-cols-3 gap-3 text-center">
                @foreach ([['Layak', 'Trip berjalan normal'], ['Waspada', 'Jalan dengan pengawasan'], ['Tidak Layak', 'Ditunda, jadwal ulang']] as [$d, $t])
                    <div class="rounded-2xl bg-sand-50 p-4">
                        <x-decision :value="$d"/>
                        <p class="mt-3 text-xs text-stone-600">{{ $t }}</p>
                    </div>
                @endforeach
                <div class="col-span-3 grid grid-cols-5 gap-2 rounded-2xl bg-sea-50 p-4 text-sea-800">
                    @foreach ([['sun', 'Cuaca'], ['wind', 'Angin'], ['wave', 'Gelombang'], ['eye', 'Pandang'], ['tide', 'Pasut']] as [$i, $l])
                        <div><x-icon :name="$i" class="mx-auto size-6"/><p class="mt-1 text-[11px] font-semibold">{{ $l }}</p></div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @if ($reviews->isNotEmpty())
        <section class="container-x pb-16 lg:pb-24">
            <p class="eyebrow">Kata tamu</p>
            <h2 class="h-display mt-3 text-3xl sm:text-4xl">Mereka yang sudah berlayar bersama kami</h2>
            <div class="mt-8 grid gap-4 md:grid-cols-3">
                @foreach ($reviews as $review)
                    <x-review-card :review="$review" show-boat/>
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="container-x">
        <div class="relative overflow-hidden rounded-3xl bg-coral-500 px-6 py-12 text-white sm:px-12">
            <svg class="absolute -right-10 -bottom-10 size-64 text-white/10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="12"/></svg>
            <h2 class="h-display relative max-w-xl text-3xl text-white sm:text-4xl">Sudah tahu mau berangkat tanggal berapa?</h2>
            <p class="relative mt-3 max-w-lg text-coral-50">Isi pesanan dulu, daftar akun belakangan. Pilihan Anda tidak akan hilang.</p>
            <a href="{{ route('boats.index') }}" class="btn relative mt-6 bg-white text-coral-700 hover:bg-sand-50">Pesan perahu sekarang <x-icon name="arrow" class="size-4"/></a>
        </div>
    </section>
</x-layouts.app>
