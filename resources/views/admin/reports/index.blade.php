<x-layouts.admin title="Laporan">
    <x-slot:actions>
        <button onclick="window.print()" class="btn-ghost btn-sm">Cetak</button>
    </x-slot:actions>
    <form method="get" class="mb-6 flex items-end gap-3">
        <div><label class="label" for="bulan">Bulan</label><input id="bulan" type="month" name="bulan" value="{{ $start->format('Y-m') }}" class="input"></div>
        <button class="btn-sea">Tampilkan</button>
    </form>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-6">
        @foreach ([['Trip', $summary['trips']], ['Penumpang', $summary['passengers']], ['Sewa perahu', rupiah($summary['trip'])], ['Paket makan', rupiah($summary['meals'])], ['Sewa alat', rupiah($summary['rentals'])], ['Total', rupiah($summary['total'])]] as [$l, $v])
            <div class="card p-4"><p class="text-xs text-stone-500">{{ $l }}</p><p class="num mt-1 font-display text-xl font-semibold">{{ $v }}</p></div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_320px]">
        <section class="card overflow-x-auto">
            <table class="table-base">
                <thead><tr><th>Tanggal</th><th>Kode</th><th>Perahu</th><th>Pemesan</th><th class="text-right">Org</th><th class="text-right">Total</th></tr></thead>
                <tbody>
                    @forelse ($bookings as $b)
                        <tr><td>{{ $b->trip_date->format('d/m') }}</td><td class="num font-mono text-xs">{{ $b->code }}</td><td>{{ $b->boat->name }}</td><td>{{ $b->contact_name }}</td><td class="num text-right">{{ $b->passengers }}</td><td class="num text-right font-semibold">{{ rupiah($b->total) }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-stone-500">Tidak ada trip terkonfirmasi di {{ $start->translatedFormat('F Y') }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <section class="card p-5">
            <h2 class="font-display text-lg font-semibold">Per perahu</h2>
            @php($max = max($byBoat->max('total') ?? 0, 1))
            <div class="mt-4 space-y-3">
                @forelse ($byBoat as $name => $row)
                    <div>
                        <div class="flex justify-between text-sm"><span class="font-semibold">{{ $name }}</span><span class="num text-stone-600">{{ $row['trips'] }} trip</span></div>
                        <div class="mt-1 h-2 rounded-full bg-sand-100"><div class="h-2 rounded-full bg-sea-500" style="width: {{ $row['total'] / $max * 100 }}%"></div></div>
                        <p class="num mt-0.5 text-xs text-stone-500">{{ rupiah($row['total']) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-stone-500">Belum ada data.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.admin>
