<x-layouts.app title="Checkout">
    @php($boat = $quote['boat'])
    <div class="container-x py-8 sm:py-12">
        <p class="eyebrow">Langkah 3 dari 3 · Checkout</p>
        <h1 class="h-display mt-2 text-3xl sm:text-4xl">Periksa pesanan Anda</h1>
        <div class="mt-6"><x-flash/></div>

        <div class="grid gap-8 lg:grid-cols-[1fr_380px]">
            <div class="order-2 lg:order-1">
                @auth
                    <form action="{{ route('booking.confirm') }}" method="post" class="card p-5 sm:p-7">
                        @csrf
                        <h2 class="font-display text-xl font-semibold">Data kontak rombongan</h2>
                        <p class="mt-1 text-sm text-stone-600">Nakhoda akan menghubungi nomor ini sehari sebelum berangkat.</p>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="contact_name" class="label">Nama penanggung jawab</label>
                                <input id="contact_name" name="contact_name" value="{{ old('contact_name', auth()->user()->name) }}" required class="input">
                                <x-error name="contact_name"/>
                            </div>
                            <div>
                                <label for="contact_phone" class="label">Nomor WhatsApp</label>
                                <input id="contact_phone" name="contact_phone" inputmode="tel" value="{{ old('contact_phone', auth()->user()->phone) }}" required class="input" placeholder="08xxxxxxxxxx">
                                <x-error name="contact_phone"/>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="notes" class="label">Catatan untuk nakhoda <span class="font-normal text-stone-500">(opsional)</span></label>
                                <textarea id="notes" name="notes" rows="3" class="input" placeholder="mis. ada 2 anak kecil, minta berhenti lebih lama di Gili Kedis">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <label class="mt-5 flex items-start gap-3 rounded-xl bg-sand-50 p-4 text-sm">
                            <input type="checkbox" name="agree" value="1" @checked(old('agree')) class="mt-0.5 size-4 rounded border-sand-300 text-sea-600 focus:ring-sea-500">
                            <span>Saya memahami bahwa trip dapat <strong>dijadwalkan ulang</strong> bila kondisi laut dinilai tidak layak, dan pembayaran harus diselesaikan dalam {{ \App\Models\Setting::get('payment_deadline_hours') }} jam.</span>
                        </label>
                        <x-error name="agree"/>
                        <x-error name="trip_date"/>
                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ route('boats.show', $boat) }}" class="text-sm font-semibold text-sea-700 hover:text-coral-600">← Ubah pilihan</a>
                            <button class="btn-primary py-3">Buat pesanan · <span class="num">{{ rupiah($quote['total']) }}</span></button>
                        </div>
                    </form>
                @else
                    <div x-data="{ tab: @js($errors->has('login') ? 'login' : 'register') }" class="card overflow-hidden">
                        <div class="bg-sea-50 p-5 sm:p-7">
                            <h2 class="font-display text-xl font-semibold">Satu langkah lagi: masuk atau daftar</h2>
                            <p class="mt-1 text-sm text-stone-700">Pesanan di samping sudah kami simpan. Setelah masuk atau daftar, Anda langsung kembali ke halaman ini.</p>
                        </div>
                        <div class="flex border-b border-sand-200" role="tablist">
                            <button type="button" role="tab" @click="tab = 'register'" :aria-selected="tab === 'register'" class="flex-1 px-4 py-3 text-sm font-semibold" :class="tab === 'register' ? 'border-b-2 border-coral-500 text-sea-950' : 'text-stone-500'">Saya pengguna baru</button>
                            <button type="button" role="tab" @click="tab = 'login'" :aria-selected="tab === 'login'" class="flex-1 px-4 py-3 text-sm font-semibold" :class="tab === 'login' ? 'border-b-2 border-coral-500 text-sea-950' : 'text-stone-500'">Sudah punya akun</button>
                        </div>
                        <div class="p-5 sm:p-7">
                            <div x-show="tab === 'register'" x-cloak>@include('auth._register-form')</div>
                            <div x-show="tab === 'login'">@include('auth._login-form')</div>
                        </div>
                    </div>
                @endauth
            </div>

            {{-- Ringkasan --}}
            <aside class="order-1 lg:order-2">
                <div class="card overflow-hidden lg:sticky lg:top-24">
                    <div class="flex gap-4 border-b border-sand-200 p-4">
                        <div class="w-24 shrink-0 overflow-hidden rounded-xl"><x-boat-photo :boat="$boat" class="aspect-square w-full"/></div>
                        <div class="min-w-0">
                            <p class="text-xs text-stone-500">{{ \App\Models\Booking::TRIP_TYPES[$draft['trip_type'] ?? 'gili'] }} · Dermaga Tawun</p>
                            <p class="font-display text-lg font-semibold">{{ $boat->name }}</p>
                            <p class="mt-1 text-sm text-stone-700">{{ \Illuminate\Support\Carbon::parse($draft['trip_date'])->translatedFormat('l, d F Y') }}</p>
                            <p class="text-sm text-stone-700">Berangkat {{ $draft['departure_time'] }} WITA · {{ $quote['passengers'] }} orang</p>
                        </div>
                    </div>
                    @if ($condition)
                        <div class="flex items-center justify-between gap-2 border-b border-sand-200 px-4 py-3 text-xs">
                            <span class="text-stone-600">Prakiraan laut</span>
                            <x-decision :value="$condition->decision()"/>
                        </div>
                    @endif
                    <dl class="space-y-2 p-4 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-stone-600">{{ \App\Models\Booking::TRIP_TYPES[$draft['trip_type'] ?? 'gili'] }} · 1 perahu</dt><dd class="num">{{ rupiah($quote['trip_price']) }}</dd></div>
                        @foreach ($quote['meals'] as $meal)
                            <div class="flex justify-between gap-3"><dt class="text-stone-600">{{ $meal['name'] }} × {{ $meal['qty'] }}</dt><dd class="num">{{ rupiah($meal['subtotal']) }}</dd></div>
                        @endforeach
                        @foreach ($quote['rentals'] as $item)
                            <div class="flex justify-between gap-3"><dt class="text-stone-600">{{ $item['name'] }} × {{ $item['qty'] }}</dt><dd class="num">{{ rupiah($item['subtotal']) }}</dd></div>
                        @endforeach
                        <div class="flex items-baseline justify-between border-t border-dashed border-sand-300 pt-3">
                            <dt class="font-semibold">Total bayar</dt>
                            <dd class="num font-display text-2xl font-semibold text-sea-950">{{ rupiah($quote['total']) }}</dd>
                        </div>
                    </dl>
                    <form action="{{ route('booking.discard') }}" method="post" class="border-t border-sand-200 px-4 py-3 text-center">
                        @csrf
                        <button class="text-xs font-semibold text-stone-500 hover:text-coral-600">Batalkan & mulai ulang</button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.app>
