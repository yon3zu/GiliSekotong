<x-layouts.app :title="'Pesanan '.$booking->code">
    @php
        $steps = ['Pesanan dibuat', 'Bayar & unggah bukti', 'Diverifikasi admin', 'Berlayar'];
        $current = match ($booking->status) {
            'menunggu_pembayaran' => 1, 'menunggu_verifikasi' => 2, 'terkonfirmasi' => 3, 'selesai' => 4, default => 0,
        };
        $condition = $booking->seaCondition;
    @endphp
    <div class="container-x py-8 sm:py-10">
        <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-sea-700 hover:text-coral-600"><x-icon name="back" class="size-4"/> Pesanan saya</a>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <h1 class="h-display text-3xl sm:text-4xl">Pesanan <span class="num font-mono text-2xl sm:text-3xl">{{ $booking->code }}</span></h1>
            <x-status :booking="$booking" class="text-sm"/>
        </div>
        <div class="mt-6"><x-flash/></div>

        @if ($booking->status !== 'dibatalkan')
            <ol class="mb-8 grid grid-cols-4 gap-2 text-center text-[11px] font-semibold sm:text-xs">
                @foreach ($steps as $i => $step)
                    <li>
                        <div @class(['mx-auto mb-2 h-1.5 rounded-full', 'bg-sea-600' => $i < $current, 'bg-coral-500' => $i === $current, 'bg-sand-200' => $i > $current])></div>
                        <span @class(['text-sea-900' => $i <= $current, 'text-stone-400' => $i > $current])>{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
        @endif

        <div class="grid gap-8 lg:grid-cols-[1fr_380px]">
            <div class="space-y-6">
                @if ($booking->status === 'menunggu_pembayaran' || $booking->status === 'menunggu_verifikasi')
                    <section class="card p-5 sm:p-7">
                        <h2 class="font-display text-xl font-semibold">Pembayaran</h2>
                        @if ($booking->status === 'menunggu_pembayaran')
                            <p class="mt-1 text-sm text-stone-600">Selesaikan sebelum <strong>{{ $deadline->translatedFormat('d M Y, H:i') }}</strong>.</p>
                        @else
                            <p class="mt-1 text-sm text-stone-600">Bukti transfer sudah diterima {{ $booking->paid_at->diffForHumans() }}. Admin sedang memeriksa. Anda masih bisa mengganti foto bila keliru.</p>
                        @endif
                        @if ($booking->admin_note && $booking->status === 'menunggu_pembayaran')
                            <p class="mt-3 rounded-xl bg-coral-50 p-3 text-sm text-coral-700">Catatan admin: {{ $booking->admin_note }}</p>
                        @endif

                        @if ($booking->status === 'menunggu_pembayaran' && \App\Services\QrisPayment::available() && $booking->user_id === auth()->id())
                            @include('orders._qris')
                            <p class="mt-6 text-sm font-semibold text-sea-950">Atau transfer bank</p>
                        @endif

                        <div x-data="{ copied: false, copiedAmount: false }" class="mt-3 rounded-2xl bg-sea-900 p-5 text-white">
                            <p class="text-xs text-sea-200">{{ $bank['name'] }} · a.n. {{ $bank['holder'] }}</p>
                            <div class="mt-1 flex items-center justify-between gap-3">
                                <p class="num font-mono text-xl font-semibold tracking-wider sm:text-2xl">{{ $bank['account'] }}</p>
                                <button type="button" class="btn-sm btn bg-white/10 text-white hover:bg-white/20"
                                        @click="navigator.clipboard.writeText(@js(preg_replace('/\D/', '', $bank['account']))); copied = true; setTimeout(() => copied = false, 1500)"
                                        x-text="copied ? 'Tersalin' : 'Salin'">Salin</button>
                            </div>
                            <div class="mt-3 border-t border-white/10 pt-3">
                                <p class="text-xs text-sea-200">Jumlah transfer</p>
                                <div class="mt-1 flex items-center justify-between gap-3">
                                    <p class="num font-mono text-xl font-semibold tracking-wider sm:text-2xl">{{ rupiah($booking->transferAmount()) }}</p>
                                    <button type="button" class="btn-sm btn bg-white/10 text-white hover:bg-white/20"
                                            @click="navigator.clipboard.writeText(@js((string) $booking->transferAmount())); copiedAmount = true; setTimeout(() => copiedAmount = false, 1500)"
                                            x-text="copiedAmount ? 'Tersalin' : 'Salin'">Salin</button>
                                </div>
                                <p class="mt-2 text-xs leading-relaxed text-sea-200">Sudah termasuk kode unik <span class="num font-semibold text-white">{{ $booking->uniqueCodeLabel() }}</span>. Transfer <strong class="text-white">persis sampai 3 digit terakhir</strong>, jangan dibulatkan, supaya pembayaran Anda mudah dikenali.</p>
                            </div>
                        </div>

                        <form action="{{ route('orders.proof', $booking) }}" method="post" enctype="multipart/form-data" class="mt-5" x-data="{ name: '' }">
                            @csrf
                            <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-sand-300 p-6 text-center hover:border-sea-400 hover:bg-sea-50/40">
                                <x-icon name="upload" class="size-7 text-sea-600"/>
                                <span class="text-sm font-semibold" x-text="name || 'Pilih foto bukti transfer'">Pilih foto bukti transfer</span>
                                <span class="text-xs text-stone-500">JPG / PNG, maks. 3 MB</span>
                                <input type="file" name="proof" accept="image/*" required class="sr-only" @change="name = $event.target.files[0]?.name">
                            </label>
                            <x-error name="proof"/>
                            <button class="btn-primary mt-4 w-full py-3">{{ $booking->payment_proof ? 'Ganti bukti transfer' : 'Kirim bukti transfer' }}</button>
                        </form>
                        @if ($booking->payment_proof)
                            <a href="{{ route('orders.proof.view', $booking) }}" target="_blank" class="mt-3 inline-block text-sm font-semibold text-sea-700 hover:underline">Lihat bukti yang terkirim</a>
                        @endif
                    </section>
                @endif

                @if ($booking->canBeCompletedByCustomer())
                    <section class="card flex flex-col items-start gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-7">
                        <div>
                            <h2 class="font-display text-xl font-semibold">Sudah kembali dari trip?</h2>
                            <p class="mt-1 text-sm text-stone-600">Tandai selesai lalu beri ulasan. Bila tidak ditandai, pesanan selesai otomatis {{ \App\Models\Setting::get('auto_complete_days') }} hari setelah tanggal trip.</p>
                        </div>
                        <form action="{{ route('orders.complete', $booking) }}" method="post" class="shrink-0" onsubmit="return confirm('Tandai pesanan ini selesai?')">
                            @csrf
                            <button class="btn-sea"><x-icon name="check" class="size-4"/> Selesaikan pesanan</button>
                        </form>
                    </section>
                @endif

                @if ($booking->status === 'selesai' && $booking->user_id === auth()->id())
                    @php $review = $booking->review; @endphp
                    <section id="ulasan" class="card scroll-mt-20 p-5 sm:p-7">
                        <h2 class="font-display text-xl font-semibold">{{ $review ? 'Ulasan Anda' : 'Bagaimana trip Anda?' }}</h2>
                        <p class="mt-1 text-sm text-stone-600">Ulasan tampil di halaman perahu {{ $booking->boat->name }} dengan nama disingkat.</p>
                        <form action="{{ route('orders.review', $booking) }}" method="post" class="mt-4 space-y-4" x-data="{ rating: {{ (int) old('rating', $review?->rating) }}, hover: 0 }">
                            @csrf
                            <fieldset>
                                <legend class="label">Penilaian</legend>
                                <div class="flex gap-1" @mouseleave="hover = 0">
                                    @foreach (range(1, 5) as $n)
                                        <label class="cursor-pointer p-1" @mouseenter="hover = {{ $n }}">
                                            <input type="radio" name="rating" value="{{ $n }}" x-model.number="rating" class="peer sr-only" required>
                                            <span class="block rounded peer-focus-visible:ring-2 peer-focus-visible:ring-sea-500" :class="(hover || rating) >= {{ $n }} ? 'text-amber-500' : 'text-sand-300'">
                                                <svg class="size-8" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9z"/></svg>
                                            </span>
                                            <span class="sr-only">{{ $n }} bintang</span>
                                        </label>
                                    @endforeach
                                </div>
                                <x-error name="rating"/>
                            </fieldset>
                            <div>
                                <label class="label" for="comment">Cerita singkat <span class="font-normal text-stone-500">(opsional)</span></label>
                                <textarea id="comment" name="comment" rows="3" maxlength="1000" class="input" placeholder="Perahunya, nakhodanya, snorkelingnya...">{{ old('comment', $review?->comment) }}</textarea>
                                <x-error name="comment"/>
                            </div>
                            <button class="btn-primary">{{ $review ? 'Perbarui ulasan' : 'Kirim ulasan' }}</button>
                        </form>
                    </section>
                @endif

                @if ($booking->hasTicket())
                    <section class="flex flex-col items-start gap-4 rounded-2xl bg-sea-900 p-6 text-white sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            @if ($booking->status === 'selesai')
                                <p class="font-display text-xl font-semibold">Arsip e-tiket</p>
                                <p class="mt-1 text-sm text-sea-200">Trip ini sudah selesai. E-tiket tetap bisa dibuka sebagai bukti perjalanan.</p>
                            @else
                                <p class="font-display text-xl font-semibold">E-tiket sudah terbit</p>
                                <p class="mt-1 text-sm text-sea-200">Tunjukkan QR di Dermaga Tawun 30 menit sebelum {{ $booking->departure_time }} WITA.</p>
                            @endif
                        </div>
                        <a href="{{ route('orders.ticket', $booking) }}" class="btn-primary shrink-0"><x-icon name="ticket" class="size-4"/> Buka e-tiket</a>
                    </section>
                @endif

                <section class="card p-5 sm:p-7">
                    <h2 class="font-display text-xl font-semibold">Kondisi laut di hari trip</h2>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <x-decision :value="$condition?->decision()" size="lg"/>
                        <span class="text-sm text-stone-600">{{ \App\Support\Sailing::advice($condition?->decision()) }}</span>
                    </div>
                    @if ($condition)
                        <p class="mt-3 text-xs text-stone-500">{{ $condition->cuaca }} · angin {{ $condition->wind_knot }} knot · gelombang {{ $condition->wave_m }} m · jarak pandang {{ strtolower($condition->jarak_pandang) }}</p>
                    @endif
                </section>

                @if ($booking->status === 'menunggu_pembayaran')
                    <form action="{{ route('orders.cancel', $booking) }}" method="post" onsubmit="return confirm('Batalkan pesanan ini?')">
                        @csrf
                        <button class="text-sm font-semibold text-stone-500 hover:text-coral-600">Batalkan pesanan</button>
                    </form>
                @endif
            </div>

            <aside>
                <div class="card overflow-hidden lg:sticky lg:top-24">
                    <x-boat-photo :boat="$booking->boat" class="aspect-[16/9] w-full"/>
                    <div class="p-5">
                        <p class="text-xs text-stone-500">{{ $booking->tripLabel() }}</p><p class="font-display text-xl font-semibold">{{ $booking->boat->name }}</p>
                        <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-xs text-stone-500">Tanggal</dt><dd class="font-semibold">{{ $booking->trip_date->translatedFormat('d M Y') }}</dd></div>
                            <div><dt class="text-xs text-stone-500">Berangkat</dt><dd class="font-semibold">{{ $booking->departure_time }} WITA</dd></div>
                            <div><dt class="text-xs text-stone-500">Penumpang</dt><dd class="font-semibold">{{ $booking->passengers }} orang</dd></div>
                            <div><dt class="text-xs text-stone-500">Kontak</dt><dd class="font-semibold">{{ $booking->contact_phone }}</dd></div>
                        </dl>
                        <dl class="mt-4 space-y-2 border-t border-dashed border-sand-300 pt-4 text-sm">
                            <div class="flex justify-between"><dt class="text-stone-600">{{ $booking->tripLabel() }}</dt><dd class="num">{{ rupiah($booking->trip_price) }}</dd></div>
                            @foreach ($booking->meals as $meal)
                                <div class="flex justify-between gap-3"><dt class="text-stone-600">{{ $meal->name }} × {{ $meal->qty }}</dt><dd class="num">{{ rupiah($meal->subtotal) }}</dd></div>
                            @endforeach
                            @foreach ($booking->rentals as $item)
                                <div class="flex justify-between gap-3"><dt class="text-stone-600">{{ $item->name }} × {{ $item->qty }}</dt><dd class="num">{{ rupiah($item->subtotal) }}</dd></div>
                            @endforeach
                            <div class="flex items-baseline justify-between pt-2"><dt class="font-semibold">Total</dt><dd class="num font-display text-2xl font-semibold">{{ rupiah($booking->total) }}</dd></div>
                        </dl>
                        @if ($booking->notes)
                            <p class="mt-4 rounded-xl bg-sand-50 p-3 text-xs text-stone-600">Catatan: {{ $booking->notes }}</p>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.app>
