<x-layouts.app title="Cara Pesan">
    <section class="border-b border-sand-200 bg-sand-100">
        <div class="container-x py-12 sm:py-16">
            <p class="eyebrow">Cara pesan & bayar</p>
            <h1 class="h-display mt-3 text-4xl sm:text-5xl">Lima langkah sampai naik perahu.</h1>
        </div>
    </section>
    <div class="container-x py-14">
        <ol class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['Pilih perahu', 'Sesuaikan dengan jumlah rombongan (maksimal 12 orang per perahu). Harga sewa per perahu, bukan per orang.'],
                ['Atur perjalanan', 'Pilih trip 3 gili atau sewa mancing, tanggal dan jam berangkat, jumlah penumpang, lalu tambahkan paket makan dan sewa alat bila perlu.'],
                ['Checkout', 'Belum punya akun? Daftar langsung di halaman checkout. Pilihan Anda tetap tersimpan, tidak perlu isi ulang.'],
                ['Bayar QRIS atau transfer', 'Bayar dengan QRIS, atau transfer ke rekening yang tertera sesuai nominal berkode unik (3 digit terakhir), lalu unggah foto bukti transfer di halaman pesanan.'],
                ['Terima e-tiket', 'Setelah admin memverifikasi, e-tiket dengan kode QR terbit. Tunjukkan di Dermaga Tawun saat berangkat.'],
            ] as $i => [$title, $text])
                <li class="card p-6">
                    <span class="num font-display text-4xl font-semibold text-coral-500">{{ $i + 1 }}</span>
                    <h2 class="mt-3 font-display text-xl font-semibold">{{ $title }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $text }}</p>
                </li>
            @endforeach
            <li class="flex flex-col justify-between rounded-2xl bg-coral-500 p-6 text-white">
                <p class="font-display text-xl font-semibold">Siap berangkat?</p>
                <a href="{{ route('boats.index') }}" class="btn mt-6 self-start bg-white text-coral-700">Pilih perahu <x-icon name="arrow" class="size-4"/></a>
            </li>
        </ol>

        <div class="mt-14 grid gap-6 lg:grid-cols-2">
            <section class="card p-6">
                <h2 class="font-display text-xl font-semibold">Rincian biaya</h2>
                <table class="table-base mt-4">
                    <tbody>
                        <tr><td>Sewa perahu, trip 3 gili atau mancing (bensin & nakhoda)</td><td class="num text-right font-semibold">Rp350.000 / perahu</td></tr>
                        @foreach ($meals as $meal)
                            <tr><td>{{ $meal->name }} <span class="block text-xs text-stone-500">{{ implode(', ', $meal->menu) }}</span></td><td class="num text-right font-semibold whitespace-nowrap">{{ rupiah($meal->price) }} / paket</td></tr>
                        @endforeach
                        @foreach ($rentals as $item)
                            <tr><td>Sewa {{ strtolower($item->name) }}</td><td class="num text-right font-semibold whitespace-nowrap">{{ rupiah($item->price) }} / pcs</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
            <section class="card p-6">
                <h2 class="font-display text-xl font-semibold">Ketentuan</h2>
                <ul class="mt-4 space-y-3 text-sm leading-relaxed text-stone-700">
                    <li>Pemesanan paling cepat H-1 dan paling lambat 90 hari sebelum trip.</li>
                    <li>Satu perahu hanya melayani satu rombongan per hari, jadi perahu Anda tidak dibagi dengan orang lain.</li>
                    <li>Pembayaran diselesaikan dalam {{ \App\Models\Setting::get('payment_deadline_hours') }} jam setelah pesanan dibuat.</li>
                    <li>Bila kondisi laut dinilai <strong>tidak layak</strong>, trip dijadwalkan ulang tanpa biaya.</li>
                    <li>Pembatalan oleh pemesan dapat dilakukan selama pesanan belum dibayar.</li>
                </ul>
            </section>
        </div>
    </div>
</x-layouts.app>
