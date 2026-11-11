<x-layouts.admin :title="'Pesanan '.$booking->code">
    @php $condition = $booking->seaCondition; @endphp
    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            <section class="card p-5">
                <div class="flex flex-wrap items-center gap-3">
                    <x-status :booking="$booking" class="text-sm"/>
                    @if ($booking->checked_in_at)<span class="chip tone-layak">Check-in {{ $booking->checked_in_at->format('d/m H:i') }}</span>@endif
                    @if ($booking->completed_at)<span class="chip tone-none">Selesai {{ $booking->completed_at->format('d/m H:i') }}</span>@endif
                    <span class="text-xs text-stone-500">Dibuat {{ $booking->created_at->translatedFormat('d M Y H:i') }}</span>
                </div>
                <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-3">
                    <div><dt class="text-xs text-stone-500">Keperluan</dt><dd class="font-semibold">{{ $booking->tripLabel() }}</dd></div>
                    <div><dt class="text-xs text-stone-500">Perahu</dt><dd class="font-semibold">{{ $booking->boat->name }} ({{ $booking->boat->capacity }} org)</dd></div>
                    <div><dt class="text-xs text-stone-500">Tanggal & jam</dt><dd class="font-semibold">{{ $booking->trip_date->translatedFormat('l, d M Y') }} · {{ $booking->departure_time }}</dd></div>
                    <div><dt class="text-xs text-stone-500">Penumpang</dt><dd class="font-semibold">{{ $booking->passengers }} orang</dd></div>
                    <div><dt class="text-xs text-stone-500">Kontak</dt><dd class="font-semibold">{{ $booking->contact_name }}</dd></div>
                    <div><dt class="text-xs text-stone-500">WhatsApp</dt><dd><a class="font-semibold text-sea-700 hover:underline" target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $booking->contact_phone)) }}">{{ $booking->contact_phone }}</a></dd></div>
                    <div><dt class="text-xs text-stone-500">Akun</dt><dd class="font-semibold">{{ $booking->user->email }}</dd></div>
                </dl>
                @if ($booking->notes)<p class="mt-4 rounded-xl bg-sand-50 p-3 text-sm">Catatan pemesan: {{ $booking->notes }}</p>@endif
                @if ($booking->admin_note)<p class="mt-2 rounded-xl bg-sea-50 p-3 text-sm">Catatan admin: {{ $booking->admin_note }}</p>@endif
            </section>

            <section class="card p-5">
                <h2 class="font-display text-lg font-semibold">Rincian biaya</h2>
                <table class="table-base mt-2">
                    <tbody>
                        <tr><td>{{ $booking->tripLabel() }} (1 perahu)</td><td class="num text-right">{{ rupiah($booking->trip_price) }}</td></tr>
                        @foreach ($booking->meals as $m)<tr><td>{{ $m->name }} × {{ $m->qty }} @ {{ rupiah($m->price) }}</td><td class="num text-right">{{ rupiah($m->subtotal) }}</td></tr>@endforeach
                        @foreach ($booking->rentals as $r)<tr><td>{{ $r->name }} × {{ $r->qty }} @ {{ rupiah($r->price) }}</td><td class="num text-right">{{ rupiah($r->subtotal) }}</td></tr>@endforeach
                        <tr><td class="font-bold">Total</td><td class="num text-right font-display text-lg font-semibold">{{ rupiah($booking->total) }}</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card p-5">
                <h2 class="font-display text-lg font-semibold">Kondisi laut di tanggal trip</h2>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <x-decision :value="$condition?->decision()" size="lg"/>
                    @if ($condition)<span class="text-sm text-stone-600">{{ $condition->cuaca }} · angin {{ $condition->wind_knot + 0 }} kn ({{ $condition->angin }}) · gelombang {{ $condition->wave_m + 0 }} m ({{ $condition->gelombang }})</span>@endif
                </div>
                @if ($condition?->blocksBooking() && ! in_array($booking->status, ['selesai', 'dibatalkan']))
                    <p class="mt-3 rounded-xl bg-red-50 p-3 text-sm text-red-800">Laut dinilai tidak layak. Hubungi pemesan dan pindahkan jadwal di panel samping.</p>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            @if ($booking->payment_proof)
                <section class="card overflow-hidden">
                    <a href="{{ route('orders.proof.view', $booking) }}" target="_blank"><img src="{{ route('orders.proof.view', $booking) }}" alt="Bukti transfer" class="max-h-80 w-full bg-sand-100 object-contain"></a>
                    <p class="px-4 py-2 text-xs text-stone-500">Bukti transfer · {{ $booking->paid_at?->translatedFormat('d M Y H:i') }}</p>
                </section>
            @endif

            @if ($booking->qris_order_id)
                <section class="card space-y-1 p-5 text-sm">
                    <h2 class="font-display text-lg font-semibold">Pembayaran QRIS</h2>
                    <p class="num font-mono text-xs break-all text-stone-600">{{ $booking->qris_order_id }}</p>
                    <p>Status: <span class="font-semibold">{{ ['pending' => 'Menunggu dibayar', 'settlement' => 'Lunas', 'expire' => 'Kedaluwarsa', 'cancel' => 'Dibatalkan'][$booking->qris_status] ?? $booking->qris_status }}</span></p>
                    <p>Nominal: <span class="num font-semibold">{{ rupiah($booking->qris_amount) }}</span></p>
                    @if ($booking->qris_fee !== null)<p>Fee: <span class="num font-semibold">{{ rupiah($booking->qris_fee) }}</span> ({{ $booking->qris_fee_payer === 'merchant' ? 'ditanggung pengelola' : 'ditanggung pembeli' }})</p>@endif
                </section>
            @endif

            @if (in_array($booking->status, ['menunggu_pembayaran', 'menunggu_verifikasi']) && $booking->unique_code)
                <section class="rounded-2xl bg-sea-900 p-5 text-white">
                    <p class="text-xs text-sea-200">Nominal transfer di mutasi bank</p>
                    <p class="num mt-1 font-mono text-2xl font-semibold tracking-wider">{{ rupiah($booking->transferAmount()) }}</p>
                    <p class="mt-1 text-xs text-sea-200">{{ rupiah($booking->total) }} + kode unik {{ $booking->uniqueCodeLabel() }}</p>
                </section>
            @endif

            <section class="card space-y-3 p-5">
                <h2 class="font-display text-lg font-semibold">Tindakan</h2>
                @php
                    $actions = [];
                    if ($booking->status === 'menunggu_verifikasi') { $actions[] = ['verify', 'Verifikasi pembayaran', 'btn-primary']; $actions[] = ['reject', 'Tolak bukti transfer', 'btn-ghost']; }
                    if ($booking->status === 'menunggu_pembayaran') { $actions[] = ['verify', 'Dana sudah masuk, konfirmasi', 'btn-primary']; }
                    if ($booking->status === 'terkonfirmasi' && ! $booking->checked_in_at) { $actions[] = ['checkin', 'Check-in rombongan', 'btn-sea']; }
                    if ($booking->status === 'terkonfirmasi') { $actions[] = ['complete', 'Tandai trip selesai', 'btn-ghost']; }
                    if (! in_array($booking->status, ['selesai', 'dibatalkan'])) { $actions[] = ['cancel', 'Batalkan pesanan', 'btn-ghost text-coral-700']; }
                @endphp
                @forelse ($actions as [$action, $label, $style])
                    <form action="{{ route('admin.bookings.status', $booking) }}" method="post" @if (in_array($action, ['cancel', 'reject']) || ($action === 'verify' && $booking->status === 'menunggu_pembayaran')) onsubmit="return confirm('{{ $label }}?')" @endif>
                        @csrf
                        <input type="hidden" name="action" value="{{ $action }}">
                        @if ($action === 'reject')
                            <input name="admin_note" class="input mb-2" placeholder="Alasan (mis. nominal kurang)">
                        @endif
                        <button class="{{ $style }} w-full">{{ $label }}</button>
                    </form>
                @empty
                    <p class="text-sm text-stone-500">Tidak ada tindakan untuk status ini.</p>
                @endforelse
                @if ($booking->verifier)<p class="text-xs text-stone-500">Diverifikasi oleh {{ $booking->verifier->name }}, {{ $booking->verified_at->translatedFormat('d M H:i') }}</p>@endif
            </section>

            @if (! in_array($booking->status, ['selesai', 'dibatalkan']))
                <form action="{{ route('admin.bookings.reschedule', $booking) }}" method="post" class="card space-y-3 p-5">
                    @csrf
                    <h2 class="font-display text-lg font-semibold">Jadwalkan ulang</h2>
                    <div class="grid grid-cols-[1fr_auto] gap-2">
                        <input type="date" name="trip_date" value="{{ $booking->trip_date->toDateString() }}" min="{{ today()->toDateString() }}" class="input" required>
                        <select name="departure_time" class="input">@foreach (\App\Models\Booking::DEPARTURE_TIMES as $t)<option @selected($t === $booking->departure_time)>{{ $t }}</option>@endforeach</select>
                    </div>
                    <button class="btn-ghost w-full">Pindahkan jadwal</button>
                </form>
            @endif

            @if ($booking->review)
                <section class="card space-y-2 p-5">
                    <h2 class="font-display text-lg font-semibold">Ulasan pemesan</h2>
                    <x-stars :value="$booking->review->rating"/>
                    @if ($booking->review->comment)<p class="text-sm text-stone-700">{{ $booking->review->comment }}</p>@endif
                    <a href="{{ route('admin.reviews.index') }}" class="text-xs font-semibold text-sea-700 hover:underline">Kelola ulasan</a>
                </section>
            @endif

            @if ($booking->hasTicket())
                <a href="{{ route('orders.ticket', $booking) }}" target="_blank" class="btn-ghost w-full"><x-icon name="ticket" class="size-4"/> Lihat e-tiket</a>
            @endif
        </aside>
    </div>
</x-layouts.admin>
