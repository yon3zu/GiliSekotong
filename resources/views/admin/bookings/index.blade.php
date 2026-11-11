<x-layouts.admin title="Pemesanan">
    <div class="-mx-1 mb-4 flex gap-2 overflow-x-auto px-1 pb-1">
        <a href="{{ route('admin.bookings.index') }}" @class(['chip shrink-0 px-3 py-1.5 ring-1', 'bg-sea-800 text-white ring-sea-800' => ! request('status'), 'bg-white ring-sand-200' => request('status')])>Semua</a>
        @foreach (\App\Models\Booking::STATUSES as $key => $label)
            <a href="{{ route('admin.bookings.index', ['status' => $key]) }}" @class(['chip shrink-0 px-3 py-1.5 ring-1', 'bg-sea-800 text-white ring-sea-800' => request('status') === $key, 'bg-white ring-sand-200' => request('status') !== $key])>
                {{ $label }} <span class="num opacity-70">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <form method="get" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <div class="min-w-48 flex-1"><label class="label" for="q">Cari</label><input id="q" name="q" value="{{ request('q') }}" class="input" placeholder="Kode, nama, nomor HP, atau nominal (350.067)"></div>
        <div><label class="label" for="tanggal">Tanggal trip</label><input id="tanggal" type="date" name="tanggal" value="{{ request('tanggal') }}" class="input"></div>
        <button class="btn-sea">Terapkan</button>
        @if (request()->hasAny(['q', 'tanggal']))<a href="{{ route('admin.bookings.index', ['status' => request('status')]) }}" class="text-sm font-semibold text-coral-600">Reset</a>@endif
    </form>

    <div class="card overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Kode</th><th>Pemesan</th><th>Perahu & jadwal</th><th class="text-right">Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($bookings as $b)
                    <tr class="hover:bg-sand-50">
                        <td class="num font-mono text-xs font-semibold">{{ $b->code }}</td>
                        <td><p class="font-semibold">{{ $b->contact_name }}</p><p class="text-xs text-stone-500">{{ $b->contact_phone }}</p></td>
                        <td><p>{{ $b->boat->name }} <span class="text-xs text-stone-500">· {{ $b->tripLabel() }}</span></p><p class="text-xs text-stone-500">{{ $b->trip_date->translatedFormat('d M Y') }} · {{ $b->departure_time }} · {{ $b->passengers }} org</p></td>
                        <td class="num text-right font-semibold whitespace-nowrap">{{ rupiah($b->total) }}@if (in_array($b->status, ['menunggu_pembayaran', 'menunggu_verifikasi']) && $b->unique_code)<span class="block text-xs font-normal text-stone-500">transfer {{ rupiah($b->transferAmount()) }}</span>@endif</td>
                        <td><x-status :booking="$b"/></td>
                        <td class="text-right"><a href="{{ route('admin.bookings.show', $b) }}" class="btn-ghost btn-sm">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-stone-500">Tidak ada pesanan yang cocok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $bookings->links() }}</div>
</x-layouts.admin>
