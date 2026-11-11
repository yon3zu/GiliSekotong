<x-layouts.owner title="Riwayat Penumpang">
    <p class="mb-5 text-lg text-stone-700">Tamu online yang sudah lunas di kapal Anda, terbaru di atas.</p>
    <ul class="space-y-3">
        @forelse ($bookings as $b)
            <li class="rounded-2xl bg-white p-4 ring-1 ring-sand-200">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-lg font-bold">{{ $b->contact_name }}</p>
                        <p class="text-base text-stone-600">{{ $b->trip_date->translatedFormat('l, d F Y') }} · jam {{ $b->departure_time }}</p>
                        <p class="text-base text-stone-600">{{ $b->boat->name }} · {{ $b->tripLabel() }}</p>
                    </div>
                    <p class="num shrink-0 rounded-xl bg-sea-50 px-3 py-2 text-center text-sea-800"><span class="block font-display text-2xl font-semibold leading-none">{{ $b->passengers }}</span><span class="text-xs font-semibold">orang</span></p>
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-sand-100 pt-3">
                    <a href="tel:{{ $b->contact_phone }}" class="inline-flex items-center gap-2 text-base font-semibold text-sea-700"><x-icon name="phone" class="size-5"/> {{ $b->contact_phone }}</a>
                    <span @class(['text-sm font-semibold', 'text-emerald-700' => $b->status === 'selesai', 'text-sea-700' => $b->status !== 'selesai'])>{{ $b->trip_date->isFuture() ? 'Akan berangkat' : $b->statusLabel() }}</span>
                </div>
            </li>
        @empty
            <li class="rounded-2xl bg-white p-6 text-center text-lg text-stone-500 ring-1 ring-sand-200">Belum ada penumpang.</li>
        @endforelse
    </ul>
    <div class="mt-5">{{ $bookings->links() }}</div>
</x-layouts.owner>
