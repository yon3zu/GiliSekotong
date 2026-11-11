<x-layouts.owner title="Pembukuan">
    <div class="mb-5 flex items-center justify-between gap-2 rounded-2xl bg-white p-2 ring-1 ring-sand-200">
        <a href="{{ route('owner.books', ['bulan' => $start->copy()->subMonth()->format('Y-m')]) }}" class="rounded-xl px-4 py-3 text-lg font-bold text-sea-700" aria-label="Bulan sebelumnya">‹</a>
        <p class="text-xl font-bold">{{ $start->translatedFormat('F Y') }}</p>
        <a href="{{ route('owner.books', ['bulan' => $start->copy()->addMonth()->format('Y-m')]) }}" class="rounded-xl px-4 py-3 text-lg font-bold text-sea-700" aria-label="Bulan berikutnya">›</a>
    </div>

    <div class="mb-6 rounded-2xl bg-sea-900 p-5 text-white">
        <p class="text-base text-sea-200">Total uang sewa kapal</p>
        <p class="num font-display text-4xl font-semibold">{{ rupiah($income['total']) }}</p>
        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-white/15 pt-4 text-base">
            <div><p class="text-sea-200">Tamu online</p><p class="num text-xl font-bold">{{ rupiah($income['online_total']) }}</p><p class="text-sm text-sea-200">{{ $income['online']->count() }} trip</p></div>
            <div><p class="text-sea-200">Tamu offline</p><p class="num text-xl font-bold">{{ rupiah($income['offline_total']) }}</p><p class="text-sm text-sea-200">{{ $income['offline']->count() }} trip</p></div>
        </div>
    </div>

    <h2 class="mb-3 text-xl font-bold">Rincian</h2>
    @php($rows = $income['online']->map(fn ($b) => ['date' => $b->trip_date, 'boat' => $b->boat->name, 'who' => $b->contact_name.' · '.$b->passengers.' org', 'type' => 'Online', 'amount' => $b->trip_price])
        ->concat($income['offline']->map(fn ($o) => ['date' => $o->date, 'boat' => $o->boat->name, 'who' => ($o->note ?: 'Tamu offline').($o->passengers ? ' · '.$o->passengers.' org' : ''), 'type' => 'Offline', 'amount' => $o->amount]))
        ->sortBy('date'))
    <ul class="divide-y divide-sand-200 overflow-hidden rounded-2xl bg-white ring-1 ring-sand-200">
        @forelse ($rows as $row)
            <li class="flex items-center justify-between gap-3 p-4">
                <div class="min-w-0">
                    <p class="text-lg font-semibold">{{ $row['date']->translatedFormat('d M') }} · {{ $row['boat'] }}</p>
                    <p class="truncate text-base text-stone-600">{{ $row['who'] }}</p>
                </div>
                <div class="shrink-0 text-right">
                    <p class="num text-lg font-bold">{{ $row['amount'] !== null ? rupiah($row['amount']) : '—' }}</p>
                    <p @class(['text-sm font-semibold', 'text-sea-700' => $row['type'] === 'Online', 'text-coral-700' => $row['type'] === 'Offline'])>{{ $row['type'] }}</p>
                </div>
            </li>
        @empty
            <li class="p-6 text-center text-lg text-stone-500">Belum ada trip di bulan ini.</li>
        @endforelse
    </ul>
    <p class="mt-4 text-base text-stone-600">Tamu online = pesanan lewat website yang sudah lunas (uang sewa kapal saja, tanpa paket makan & sewa alat). Tamu offline = dari laporan Anda; bila uangnya tidak diisi tertulis "—".</p>
</x-layouts.owner>
