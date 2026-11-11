<x-layouts.app :title="$seoTitle" :description="$seoDescription" :seoImage="$boat->photoUrl()" :schema="$schema">
    @php
        $toData = fn ($items) => $items->map(fn ($m) => ['id' => $m->id, 'price' => $m->price])->values();
        $initial = old() ? [
            'trip_type' => old('trip_type'),
            'trip_date' => old('trip_date'),
            'departure_time' => old('departure_time'),
            'passengers' => (int) old('passengers'),
            'meals' => old('meals', []),
            'rentals' => old('rentals', []),
        ] : ($draft ?? []);
    @endphp

    <div class="container-x pt-6">
        <a href="{{ route('boats.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-sea-700 hover:text-coral-600"><x-icon name="back" class="size-4"/> Semua perahu</a>
    </div>

    <div class="container-x grid gap-10 py-6 lg:grid-cols-[1fr_400px] lg:gap-12"
         x-data="bookingForm({
            tripPrice: {{ $boat->trip_price }},
            capacity: {{ $boat->capacity }},
            meals: @js($toData($meals)),
            rentals: @js($toData($rentals)),
            availabilityUrl: @js(route('boats.availability', $boat)),
            initial: @js((object) $initial),
         })">

        {{-- KIRI: info perahu --}}
        <div class="min-w-0">
            <div class="overflow-hidden rounded-3xl">
                <x-boat-photo :boat="$boat" class="aspect-[16/9] w-full"/>
            </div>

            <div class="mt-8">
                <p class="eyebrow">{{ $boat->type }}</p>
                <h1 class="h-display mt-2 text-4xl sm:text-5xl">{{ $boat->name }}</h1>
                <p class="mt-4 max-w-2xl leading-relaxed text-stone-700">{{ $boat->description }}</p>
                <a href="#form-pesan" class="btn-primary mt-5 lg:hidden">Atur pesanan <span class="num" x-text="rupiah(total)"></span></a>
            </div>

            <h2 class="mt-10 font-display text-2xl font-semibold text-sea-950">Spesifikasi</h2>
            <dl class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach (array_filter([
                    ['users', 'Kapasitas', $boat->capacity.' orang'],
                    ['ruler', 'Panjang', $boat->length_m ? $boat->length_m.' meter' : null],
                    ['engine', 'Mesin', $boat->engine],
                    ['captain', 'Nakhoda', $boat->captain],
                    ['calendar', 'Tahun buat', $boat->built_year],
                    ['buoy', 'Pelampung keselamatan', $boat->capacity.' buah'],
                ], fn ($row) => $row[2]) as [$icon, $label, $value])
                    <div class="card p-4">
                        <x-icon :name="$icon" class="size-5 text-sea-600"/>
                        <dt class="mt-3 text-xs text-stone-500">{{ $label }}</dt>
                        <dd class="font-semibold text-sea-950">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($boat->facilities)
                <h2 class="mt-10 font-display text-2xl font-semibold text-sea-950">Fasilitas di perahu</h2>
                <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                    @foreach ($boat->facilities as $facility)
                        <li class="flex items-center gap-2.5 text-stone-700"><span class="flex size-6 items-center justify-center rounded-full bg-sea-50 text-sea-700"><x-icon name="check" class="size-3.5"/></span>{{ $facility }}</li>
                    @endforeach
                </ul>
            @endif

            @if ($boat->visible_reviews_count)
                <section id="ulasan" class="mt-10 scroll-mt-20">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <h2 class="font-display text-2xl font-semibold text-sea-950">Ulasan tamu</h2>
                        <p class="flex items-center gap-1.5 text-sm text-stone-600"><x-stars :value="$boat->visible_reviews_avg_rating"/><span class="num font-semibold text-sea-950">{{ number_format($boat->visible_reviews_avg_rating, 1, ',', '') }}</span> dari {{ $boat->visible_reviews_count }} ulasan</p>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($reviews as $review)
                            <x-review-card :review="$review"/>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="mt-10 rounded-2xl bg-sand-100 p-5 text-sm leading-relaxed text-stone-700">
                <p class="font-semibold text-sea-950">Yang perlu dibawa</p>
                <p class="mt-1">Baju ganti, handuk, sunblock ramah karang, topi, kantong kedap air untuk ponsel, dan uang tunai secukupnya untuk retribusi pulau atau jajan di gili. Untuk sewa mancing, bawa alat pancing dan umpan sendiri.</p>
            </div>
        </div>

        {{-- KANAN: form pemesanan --}}
        <aside id="form-pesan" class="scroll-mt-20 lg:sticky lg:top-24 lg:self-start">
            <form action="{{ route('booking.draft') }}" method="post" class="card overflow-hidden shadow-sm">
                @csrf
                <input type="hidden" name="boat_id" value="{{ $boat->id }}">
                <div class="border-b border-sand-200 bg-sea-900 px-5 py-4 text-white">
                    <p class="text-xs text-sea-200">Langkah 2 dari 3 · Atur perjalanan</p>
                    <p class="mt-0.5 font-display text-2xl font-semibold"><span class="num">{{ rupiah($boat->trip_price) }}</span> <span class="text-sm font-normal text-sea-200">/perahu</span></p>
                </div>

                <div class="space-y-5 p-5">
                    <fieldset>
                        <legend class="label">Keperluan</legend>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['gili' => ['Trip 3 Gili', 'Nanggu · Sudak · Kedis'], 'mancing' => ['Sewa Mancing', 'Perahu & nakhoda saja']] as $value => [$label, $hint])
                                <label class="cursor-pointer rounded-xl border p-3 transition" :class="tripType === '{{ $value }}' ? 'border-sea-500 bg-sea-50 ring-1 ring-sea-500' : 'border-sand-300 hover:border-sea-300'">
                                    <input type="radio" name="trip_type" value="{{ $value }}" x-model="tripType" class="sr-only">
                                    <span class="block text-sm font-semibold">{{ $label }}</span>
                                    <span class="block text-[11px] text-stone-500">{{ $hint }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1.5 text-xs text-stone-500">Harga sewa perahu sama untuk keduanya.</p>
                    </fieldset>

                    <div class="grid grid-cols-[1fr_auto] gap-3">
                        <div>
                            <label for="trip_date" class="label">Tanggal</label>
                            <input id="trip_date" type="date" name="trip_date" x-model="date" @change="checkDate()" required
                                   min="{{ today()->addDay()->toDateString() }}" max="{{ today()->addDays(90)->toDateString() }}" class="input">
                        </div>
                        <div>
                            <label for="departure_time" class="label">Jam</label>
                            <select id="departure_time" name="departure_time" x-model="time" class="input">
                                @foreach (\App\Models\Booking::DEPARTURE_TIMES as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 -mt-1">
                            <x-error name="trip_date"/>
                            <p x-show="checking" x-cloak class="text-xs text-stone-500">Memeriksa tanggal…</p>
                            <template x-if="check && !checking">
                                <div class="mt-1 rounded-xl p-3 text-xs" :class="check.booked ? 'tone-tidak' : 'tone-' + check.tone">
                                    <p class="font-bold" x-text="check.booked ? 'Perahu sudah dipesan di tanggal ini' : (check.decision ? 'Prakiraan laut: ' + check.decision : 'Prakiraan laut belum tersedia')"></p>
                                    <p class="mt-0.5" x-text="check.booked ? 'Pilih tanggal lain atau perahu lain.' : check.advice"></p>
                                    <template x-for="reason in (check.booked ? [] : check.reasons)"><p class="mt-1 font-semibold" x-text="'BMKG: ' + reason"></p></template>
                                    <p class="mt-1 opacity-70" x-show="!check.booked && check.source === 'bmkg'">Berdasarkan prakiraan cuaca BMKG Sekotong Barat.</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="label" for="passengers">Jumlah penumpang</label>
                        <div class="flex items-center gap-3">
                            <button type="button" class="size-10 rounded-full border border-sand-300 text-lg font-semibold hover:bg-sand-100" @click="setPassengers(passengers - 1)" aria-label="Kurangi penumpang">−</button>
                            <input id="passengers" name="passengers" type="number" min="1" :max="capacity" x-model.number="passengers" @change="setPassengers(passengers)" class="input num w-20 text-center text-base font-semibold">
                            <button type="button" class="size-10 rounded-full border border-sand-300 text-lg font-semibold hover:bg-sand-100" @click="setPassengers(passengers + 1)" aria-label="Tambah penumpang">+</button>
                            <span class="text-xs text-stone-500">maks. {{ $boat->capacity }}</span>
                        </div>
                        <x-error name="passengers"/>
                        <p class="mt-1.5 text-xs text-stone-500">Berapa pun jumlahnya, harga sewa tetap per perahu.</p>
                    </div>

                    <fieldset>
                        <legend class="label">Paket makan <span class="font-normal text-stone-500">(opsional · Kedai Bintang)</span></legend>
                        <div class="space-y-2">
                            @foreach ($meals as $meal)
                                <div class="rounded-xl border border-sand-200 p-3" :class="mealQty[{{ $meal->id }}] > 0 && 'border-sea-400 bg-sea-50/50'">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold">{{ $meal->name }}</p>
                                            <p class="num text-xs text-coral-600">{{ rupiah($meal->price) }} <span class="text-stone-500">/paket</span></p>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-2">
                                            <button type="button" class="size-7 rounded-full border border-sand-300 text-sm hover:bg-white" @click="stepMeal({{ $meal->id }}, -1)" aria-label="Kurangi {{ $meal->name }}">−</button>
                                            <input type="number" name="meals[{{ $meal->id }}]" min="0" max="20" x-model.number="mealQty[{{ $meal->id }}]" class="num w-8 border-0 bg-transparent p-0 text-center text-sm font-bold focus:ring-0" aria-label="Jumlah {{ $meal->name }}">
                                            <button type="button" class="size-7 rounded-full border border-sand-300 text-sm hover:bg-white" @click="stepMeal({{ $meal->id }}, 1)" aria-label="Tambah {{ $meal->name }}">+</button>
                                        </div>
                                    </div>
                                    <details class="mt-1 text-xs text-stone-600">
                                        <summary class="cursor-pointer text-sea-700">Isi paket</summary>
                                        <p class="mt-1">{{ implode(' · ', $meal->menu) }}</p>
                                    </details>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-1.5 text-xs text-stone-500">Satu paket berisi menu di atas. Jumlah paket silakan sesuaikan sendiri dengan selera rombongan.</p>
                    </fieldset>

                    @if ($rentals->isNotEmpty())
                        <fieldset>
                            <legend class="label">Sewa alat <span class="font-normal text-stone-500">(opsional, per pcs)</span></legend>
                            <div class="divide-y divide-sand-200 rounded-xl border border-sand-200">
                                @foreach ($rentals as $item)
                                    <div class="flex items-center justify-between gap-3 p-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold">{{ $item->name }}</p>
                                            <p class="num text-xs text-coral-600">{{ rupiah($item->price) }} <span class="text-stone-500">/pcs</span></p>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-2">
                                            <button type="button" class="size-7 rounded-full border border-sand-300 text-sm hover:bg-sand-100" @click="stepRental({{ $item->id }}, -1)" aria-label="Kurangi {{ $item->name }}">−</button>
                                            <input type="number" name="rentals[{{ $item->id }}]" min="0" :max="passengers" x-model.number="rentalQty[{{ $item->id }}]" class="num w-8 border-0 bg-transparent p-0 text-center text-sm font-bold focus:ring-0" aria-label="Jumlah {{ $item->name }}">
                                            <button type="button" class="size-7 rounded-full border border-sand-300 text-sm hover:bg-sand-100" @click="stepRental({{ $item->id }}, 1)" aria-label="Tambah {{ $item->name }}">+</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <x-error name="rentals.*"/>
                            <p class="mt-1.5 text-xs text-stone-500">Tiap alat paling banyak sejumlah penumpang.</p>
                        </fieldset>
                    @endif
                </div>

                <div class="border-t border-dashed border-sand-300 bg-sand-50 p-5">
                    <dl class="space-y-1.5 text-sm">
                        <div class="flex justify-between"><dt class="text-stone-600" x-text="tripType === 'mancing' ? 'Sewa mancing (1 perahu)' : 'Trip 3 gili (1 perahu)'"></dt><dd class="num" x-text="rupiah(tripPrice)"></dd></div>
                        <div class="flex justify-between" x-show="mealTotal > 0" x-cloak><dt class="text-stone-600">Paket makan × <span x-text="mealCount"></span></dt><dd class="num" x-text="rupiah(mealTotal)"></dd></div>
                        <div class="flex justify-between" x-show="rentalTotal > 0" x-cloak><dt class="text-stone-600">Sewa alat</dt><dd class="num" x-text="rupiah(rentalTotal)"></dd></div>
                        <div class="flex items-baseline justify-between border-t border-sand-200 pt-2"><dt class="font-semibold">Total</dt><dd class="num font-display text-2xl font-semibold text-sea-950" x-text="rupiah(total)"></dd></div>
                        <p class="text-right text-xs text-stone-500">≈ <span class="num" x-text="rupiah(perPerson)"></span> per orang</p>
                    </dl>
                    <button class="btn-primary mt-4 w-full py-3" :disabled="blocked">Lanjut ke checkout <x-icon name="arrow" class="size-4"/></button>
                    <p class="mt-2 text-center text-xs text-stone-500">Belum punya akun? Tidak apa-apa, pilihan Anda tersimpan.</p>
                </div>
            </form>
        </aside>
    </div>

    @if ($others->isNotEmpty())
        <section class="container-x mt-10">
            <h2 class="font-display text-2xl font-semibold text-sea-950">Perahu lainnya</h2>
            <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($others as $other)
                    @include('boats._card', ['boat' => $other])
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
