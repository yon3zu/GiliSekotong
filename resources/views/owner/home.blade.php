<x-layouts.owner title="Jadwal Kapal Saya">
    @if ($boats->isEmpty())
        <div class="rounded-2xl bg-amber-50 p-5 text-amber-900 ring-1 ring-amber-200">
            Akun Anda belum dihubungkan ke kapal. Hubungi admin agar kapal Anda didaftarkan.
        </div>
    @else
        <a href="{{ route('owner.report') }}" class="mb-6 flex items-center gap-4 rounded-2xl bg-coral-500 p-5 text-white shadow-[0_6px_0_-2px_var(--color-coral-700)]">
            <x-icon name="alert" class="size-10 shrink-0"/>
            <span>
                <span class="block text-xl font-bold">Ada tamu offline?</span>
                <span class="block text-base text-coral-50">Tekan di sini supaya kapal tidak dipesan orang lain.</span>
            </span>
        </a>

        <a href="{{ route('owner.books') }}" class="mb-6 flex items-center justify-between rounded-2xl bg-white p-5 ring-1 ring-sand-200">
            <span>
                <span class="block text-base text-stone-600">Uang sewa kapal bulan {{ now()->translatedFormat('F') }}</span>
                <span class="num block font-display text-3xl font-semibold text-sea-950">{{ rupiah($monthIncome) }}</span>
            </span>
            <x-icon name="arrow" class="size-6 text-stone-400"/>
        </a>

        @foreach ($boats as $boat)
            <section class="mb-8">
                <h2 class="mb-3 font-display text-2xl font-semibold">{{ $boat->name }}</h2>
                <ul class="divide-y divide-sand-200 overflow-hidden rounded-2xl bg-white ring-1 ring-sand-200">
                    @foreach ($days as $day)
                        @php($key = $boat->id.'|'.$day->toDateString())
                        @php($dayBookings = $bookings[$key] ?? collect())
                        @php($block = $blocks[$key] ?? null)
                        @php($sea = $conditions[$day->toDateString()] ?? null)
                        <li class="flex items-center gap-4 p-4">
                            <div class="w-16 shrink-0 text-center">
                                <p class="text-sm font-semibold text-stone-500">{{ $day->isToday() ? 'Hari ini' : ($day->isTomorrow() ? 'Besok' : $day->translatedFormat('D')) }}</p>
                                <p class="num font-display text-3xl font-semibold leading-none">{{ $day->format('d') }}</p>
                                <p class="text-xs text-stone-500">{{ $day->translatedFormat('M') }}</p>
                            </div>
                            <div class="min-w-0 flex-1">
                                @if ($dayBookings->isNotEmpty())
                                    @foreach ($dayBookings as $b)
                                        <p class="text-lg font-bold text-sea-800">Ada tamu online · {{ $b->passengers }} orang</p>
                                        <p class="text-base text-stone-700">{{ $b->contact_name }} · jam {{ $b->departure_time }}
                                            @if ($b->status !== 'terkonfirmasi' && $b->status !== 'selesai')<span class="text-amber-700">(belum lunas)</span>@endif
                                        </p>
                                        <a href="tel:{{ $b->contact_phone }}" class="mt-1 inline-flex items-center gap-1.5 text-base font-semibold text-sea-700"><x-icon name="phone" class="size-4"/> {{ $b->contact_phone }}</a>
                                    @endforeach
                                @elseif ($block)
                                    <p class="text-lg font-bold text-coral-700">{{ $block->reasonLabel() }}</p>
                                    <p class="text-base text-stone-600">{{ $block->passengers ? $block->passengers.' orang · ' : '' }}tidak bisa dipesan online</p>
                                    <form action="{{ route('owner.report.cancel', $block) }}" method="post" onsubmit="return confirm('Batalkan laporan ini? Kapal akan bisa dipesan online lagi.')">
                                        @csrf<button class="mt-1 text-base font-semibold text-stone-500 underline">Batalkan laporan</button>
                                    </form>
                                @else
                                    <p class="text-lg font-semibold text-emerald-700">Kosong</p>
                                    <p class="text-base text-stone-500">Bisa dipesan</p>
                                @endif
                            </div>
                            @if ($sea?->decision())
                                <x-decision :value="$sea->decision()" class="shrink-0"/>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    @endif
</x-layouts.owner>
