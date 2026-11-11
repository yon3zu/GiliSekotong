<x-layouts.app title="Pesanan Saya">
    <div class="container-x py-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Halo, {{ auth()->user()->name }}</p>
                <h1 class="h-display mt-2 text-4xl">Pesanan saya</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('profile.edit') }}" class="btn-ghost btn-sm">Profil</a>
                <form action="{{ route('logout') }}" method="post">@csrf<button class="btn-ghost btn-sm">Keluar</button></form>
            </div>
        </div>
        <div class="mt-6"><x-flash/></div>

        @if (session()->has('booking_draft'))
            <a href="{{ route('booking.checkout') }}" class="mb-6 flex items-center justify-between gap-3 rounded-2xl bg-coral-50 p-4 text-sm ring-1 ring-coral-100">
                <span><strong>Ada pesanan yang belum dikonfirmasi.</strong> Lanjutkan sebelum tanggalnya diambil orang lain.</span>
                <x-icon name="arrow" class="size-4 shrink-0 text-coral-600"/>
            </a>
        @endif

        <div class="space-y-3">
            @forelse ($bookings as $booking)
                <a href="{{ route('orders.show', $booking) }}" class="card flex flex-col gap-4 p-4 transition hover:border-sea-300 sm:flex-row sm:items-center">
                    <div class="w-full shrink-0 overflow-hidden rounded-xl sm:w-28"><x-boat-photo :boat="$booking->boat" class="aspect-[16/9] w-full sm:aspect-square"/></div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="num font-mono text-xs font-semibold text-stone-500">{{ $booking->code }}</p>
                            <x-status :booking="$booking"/>
                            @if ($booking->status === 'selesai' && ! $booking->review)
                                <span class="chip bg-coral-50 text-coral-700"><x-icon name="star" class="size-3.5"/> Beri ulasan</span>
                            @elseif ($booking->canBeCompletedByCustomer())
                                <span class="chip bg-sea-50 text-sea-800">Tandai selesai</span>
                            @endif
                        </div>
                        <p class="mt-1 font-display text-lg font-semibold">{{ $booking->boat->name }}</p>
                        <p class="text-sm text-stone-600">{{ $booking->tripLabel() }} · {{ $booking->trip_date->translatedFormat('l, d M Y') }} · {{ $booking->departure_time }} · {{ $booking->passengers }} orang</p>
                    </div>
                    <p class="num font-display text-xl font-semibold text-sea-950 sm:text-right">{{ rupiah($booking->total) }}</p>
                </a>
            @empty
                <div class="card p-10 text-center">
                    <p class="font-display text-xl font-semibold">Belum ada pesanan.</p>
                    <p class="mt-1 text-sm text-stone-600">Tiga gili menunggu. Pilih perahu yang pas untuk rombongan Anda.</p>
                    <a href="{{ route('boats.index') }}" class="btn-primary mt-5">Lihat perahu</a>
                </div>
            @endforelse
        </div>
        <div class="mt-6">{{ $bookings->links() }}</div>
    </div>
</x-layouts.app>
