{{-- Pilihan bayar QRIS. Selama QR tampil, halaman mengecek status dan memuat ulang saat lunas atau kedaluwarsa. --}}
<div class="mt-5 rounded-2xl border border-sand-200 p-5">
    <h3 class="font-display text-lg font-semibold">Bayar dengan QRIS</h3>

    @if ($booking->qris_status === 'pending')
        <div class="mt-3 flex flex-col items-center gap-3 text-center"
             x-data="{ async check() { const r = await fetch(@js(route('orders.qris.status', $booking)), { headers: { Accept: 'application/json' } }); if (! r.ok) return; const d = await r.json(); if (d.status !== 'pending') location.reload(); } }"
             x-init="setInterval(() => check(), 8000)">
            <img src="{{ $booking->qris_url }}" alt="Kode QRIS pesanan {{ $booking->code }}" width="256" height="256" referrerpolicy="no-referrer" class="size-64 rounded-xl bg-white p-2 ring-1 ring-sand-200">
            <p class="num font-display text-2xl font-semibold text-sea-950">{{ rupiah($booking->qris_amount) }}</p>
            @if ($booking->qris_fee_payer === 'buyer' && $booking->qris_fee)
                <p class="text-xs text-stone-500">Ada biaya layanan QRIS {{ rupiah($booking->qris_fee) }}. Periksa nominal akhir di aplikasi pembayaran Anda.</p>
            @endif
            <p class="text-sm text-stone-600">Pindai dengan aplikasi bank atau dompet digital. Pesanan terkonfirmasi otomatis setelah dibayar, halaman ini akan diperbarui sendiri.</p>
            <a href="{{ $booking->qris_url }}" target="_blank" rel="noopener noreferrer" class="btn-ghost btn-sm">Buka gambar QR (untuk bayar dari HP ini)</a>
        </div>
    @else
        @if (in_array($booking->qris_status, ['expire', 'cancel'], true))
            <p class="mt-1 rounded-xl bg-coral-50 p-3 text-sm text-coral-700">QR sebelumnya sudah tidak berlaku. Buat QR baru untuk membayar.</p>
        @else
            <p class="mt-1 text-sm text-stone-600">Lewat aplikasi bank atau dompet digital apa saja. Pesanan langsung terkonfirmasi, tanpa unggah bukti.</p>
        @endif
        <form action="{{ route('orders.qris', $booking) }}" method="post" class="mt-3">
            @csrf
            <button class="btn-sea w-full py-3 sm:w-auto">Tampilkan kode QRIS</button>
        </form>
    @endif
    <x-error name="qris"/>
</div>
