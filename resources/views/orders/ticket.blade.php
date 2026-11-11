<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>E-Tiket {{ $booking->code }} · GiliSekotong</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT,WONK@9..144,500..700,100,1&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>@media print { .no-print { display: none !important; } body { background: #fff; } @page { margin: 12mm; } }</style>
</head>
<body class="bg-sand-100 py-6 sm:py-12">
    <div class="no-print mx-auto mb-4 flex max-w-md items-center justify-between px-4">
        <a href="{{ route('orders.show', $booking) }}" class="text-sm font-semibold text-sea-700">← Kembali</a>
        <button onclick="window.print()" class="btn-sea btn-sm">Cetak / Simpan PDF</button>
    </div>

    <article class="mx-auto max-w-md px-4">
        <div class="overflow-hidden rounded-3xl bg-white shadow-xl shadow-sea-900/10">
            <header class="relative bg-sea-900 px-6 pt-6 pb-8 text-white">
                <x-logo light/>
                <p class="mt-6 text-xs tracking-[0.2em] text-sea-200 uppercase">E-Tiket {{ $booking->tripLabel() }}</p>
                <p class="mt-1 font-display text-3xl font-semibold">{{ $booking->boat->name }}</p>
                <div class="mt-4 flex items-center gap-3 text-sm">
                    <span class="font-semibold">Dermaga Tawun</span>
                    <span class="h-px flex-1 border-t border-dashed border-sea-300"></span>
                    <x-icon name="boat" class="size-5 text-coral-400"/>
                    <span class="h-px flex-1 border-t border-dashed border-sea-300"></span>
                    <span class="font-semibold">{{ $booking->trip_type === 'mancing' ? 'Spot mancing' : 'Nanggu · Sudak · Kedis' }}</span>
                </div>
            </header>

            <div class="grid grid-cols-2 gap-x-4 gap-y-4 px-6 py-6 text-sm">
                <div><p class="text-xs text-stone-500">Tanggal</p><p class="font-semibold">{{ $booking->trip_date->translatedFormat('l, d M Y') }}</p></div>
                <div><p class="text-xs text-stone-500">Berangkat</p><p class="font-semibold">{{ $booking->departure_time }} WITA</p></div>
                <div><p class="text-xs text-stone-500">Atas nama</p><p class="font-semibold">{{ $booking->contact_name }}</p></div>
                <div><p class="text-xs text-stone-500">Penumpang</p><p class="font-semibold">{{ $booking->passengers }} orang</p></div>
                <div><p class="text-xs text-stone-500">Paket makan</p><p class="font-semibold">{{ $booking->meals->map(fn ($m) => $m->name.' ×'.$m->qty)->join(', ') ?: '—' }}</p></div>
                <div><p class="text-xs text-stone-500">Sewa alat</p><p class="font-semibold">{{ $booking->rentals->map(fn ($l) => $l->name.' ×'.$l->qty)->join(', ') ?: '—' }}</p></div>
                <div><p class="text-xs text-stone-500">Nakhoda</p><p class="font-semibold">{{ $booking->boat->captain ?? '—' }}</p></div>
                <div><p class="text-xs text-stone-500">Total dibayar</p><p class="num font-semibold">{{ rupiah($booking->payment_method === 'qris' ? $booking->total : $booking->transferAmount()) }}</p></div>
            </div>

            <div class="relative flex items-center">
                <span class="absolute -left-3 size-6 rounded-full bg-sand-100"></span>
                <span class="mx-5 h-px flex-1 border-t-2 border-dashed border-sand-200"></span>
                <span class="absolute -right-3 size-6 rounded-full bg-sand-100"></span>
            </div>

            <div class="flex flex-col items-center px-6 py-6 text-center">
                <div class="rounded-2xl border border-sand-200 p-3 [&_svg]:size-44">{!! $qr !!}</div>
                <p class="num mt-3 font-mono text-xl font-bold tracking-[0.2em]">{{ $booking->code }}</p>
                <p class="mt-1 text-xs text-stone-500">Tunjukkan kode ini kepada petugas dermaga.</p>
                @if ($booking->checked_in_at)
                    <p class="chip tone-layak mt-3">Sudah check-in {{ $booking->checked_in_at->format('d/m H:i') }}</p>
                @endif
            </div>

            <footer class="bg-sand-50 px-6 py-4 text-xs leading-relaxed text-stone-600">
                Datang 30 menit sebelum berangkat. Bila kondisi laut dinilai tidak layak, admin akan menghubungi
                {{ $booking->contact_phone }} untuk penjadwalan ulang tanpa biaya.
            </footer>
        </div>
    </article>
</body>
</html>
