<x-layouts.admin title="Check-in Dermaga">
    <div class="mx-auto max-w-xl">
        <form method="get" class="card flex gap-2 p-4">
            <input name="kode" value="{{ $code }}" class="input num font-mono uppercase" placeholder="Ketik atau pindai kode tiket" autofocus>
            <button class="btn-sea shrink-0">Cek</button>
        </form>
        <p class="mt-2 text-xs text-stone-500">Memindai QR di e-tiket dengan kamera ponsel akan langsung membuka halaman ini.</p>

        @if ($code && ! $booking)
            <div class="mt-6 rounded-2xl bg-red-50 p-6 text-center text-red-800 ring-1 ring-red-200">
                <p class="font-display text-xl font-semibold">Kode tidak ditemukan</p>
                <p class="mt-1 text-sm">Periksa kembali kode {{ $code }}.</p>
            </div>
        @elseif ($booking)
            @php($ok = $booking->status === 'terkonfirmasi')
            <div @class(['mt-6 rounded-2xl p-6 ring-1', 'bg-emerald-50 ring-emerald-200' => $ok, 'bg-amber-50 ring-amber-200' => ! $ok])>
                <div class="flex items-center justify-between gap-3">
                    <p class="num font-mono text-lg font-bold">{{ $booking->code }}</p>
                    <x-status :booking="$booking"/>
                </div>
                <p class="mt-3 font-display text-2xl font-semibold">{{ $booking->contact_name }}</p>
                <p class="text-sm text-stone-700">{{ $booking->boat->name }} · {{ $booking->trip_date->translatedFormat('l, d M Y') }} · {{ $booking->departure_time }}</p>
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div><dt class="text-xs text-stone-500">Penumpang</dt><dd class="font-semibold">{{ $booking->passengers }}</dd></div>
                    <div><dt class="text-xs text-stone-500">Keperluan</dt><dd class="font-semibold">{{ $booking->tripLabel() }}</dd></div>
                    <div><dt class="text-xs text-stone-500">Paket makan</dt><dd class="font-semibold">{{ $booking->meals->map(fn ($m) => $m->name.' ×'.$m->qty)->join(', ') ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-stone-500">Sewa alat</dt><dd class="font-semibold">{{ $booking->rentals->map(fn ($l) => $l->name.' ×'.$l->qty)->join(', ') ?: '—' }}</dd></div>
                </dl>
                @if (! $booking->trip_date->isToday())
                    <p class="mt-4 text-sm font-semibold text-amber-800">Perhatian: tiket ini untuk tanggal {{ $booking->trip_date->translatedFormat('d M Y') }}, bukan hari ini.</p>
                @endif
                @if ($booking->checked_in_at)
                    <p class="mt-4 text-sm font-semibold text-emerald-800">Sudah check-in pada {{ $booking->checked_in_at->translatedFormat('d M H:i') }}.</p>
                @elseif ($ok)
                    <form action="{{ route('admin.bookings.status', $booking) }}" method="post" class="mt-5">
                        @csrf <input type="hidden" name="action" value="checkin">
                        <button class="btn-primary w-full py-3">Konfirmasi check-in</button>
                    </form>
                @else
                    <p class="mt-4 text-sm font-semibold text-amber-800">Pesanan belum terkonfirmasi, tidak bisa check-in.</p>
                @endif
            </div>
        @endif
    </div>
</x-layouts.admin>
