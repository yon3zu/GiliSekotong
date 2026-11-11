<x-layouts.owner title="Panduan Pemilik Kapal">
    <style>@media print { header, nav, .no-print { display: none !important; } body { padding: 0 !important; } }</style>
    <button onclick="window.print()" class="no-print mb-5 w-full rounded-2xl bg-white py-4 text-lg font-semibold ring-1 ring-sand-200">Cetak panduan ini</button>

    @php($steps = [
        ['Masuk ke website', 'Buka <strong>gilisekotong.com/masuk</strong>. Ketik <strong>nomor HP</strong> Anda dan <strong>kata sandi</strong> dari admin, lalu tekan <strong>Masuk</strong>.'],
        ['Lihat jadwal kapal', 'Halaman pertama adalah <strong>Jadwal</strong>. Tiap hari ada tulisan: <strong>Kosong</strong> (belum ada tamu), <strong>Ada tamu online</strong> (lengkap dengan nama & nomor HP tamu), atau <strong>Dipakai tamu offline</strong>.'],
        ['Ada tamu datang langsung?', 'Tekan tombol oranye <strong>Ada tamu offline?</strong> atau menu <strong>Lapor Tamu</strong> di bawah. Pilih kapal, pilih <strong>Hari ini / Besok</strong> atau tanggal lain, lalu tekan <strong>Kirim laporan</strong>. Selesai, kapal tidak bisa dipesan orang lain di tanggal itu.'],
        ['Salah lapor?', 'Di halaman Jadwal, pada tanggal yang dilaporkan, tekan <strong>Batalkan laporan</strong>.'],
        ['Lihat uang masuk', 'Tekan menu <strong>Pembukuan</strong>. Terlihat total uang sewa kapal bulan ini, dari tamu online dan tamu offline. Geser bulan dengan tanda ‹ dan ›.'],
        ['Lihat siapa saja yang naik', 'Tekan menu <strong>Penumpang</strong>. Ada nama, tanggal, jumlah orang, dan nomor HP yang bisa langsung ditelepon.'],
        ['Warna status laut', '<span class="chip tone-layak">Layak</span> aman berangkat, <span class="chip tone-waspada">Waspada</span> hati-hati, <span class="chip tone-tidak">Tidak Layak</span> jangan berangkat. Datanya dari BMKG.'],
    ])
    <ol class="space-y-4">
        @foreach ($steps as $i => [$title, $text])
            <li class="flex gap-4 rounded-2xl bg-white p-5 ring-1 ring-sand-200">
                <span class="num flex size-12 shrink-0 items-center justify-center rounded-full bg-coral-500 font-display text-2xl font-semibold text-white">{{ $i + 1 }}</span>
                <div>
                    <p class="text-xl font-bold">{{ $title }}</p>
                    <p class="mt-1 text-lg text-stone-700">{!! $text !!}</p>
                </div>
            </li>
        @endforeach
    </ol>

    <div class="mt-6 rounded-2xl bg-sea-50 p-5 text-lg ring-1 ring-sea-200">
        <p class="font-bold">Butuh bantuan?</p>
        <p>Hubungi admin GiliSekotong: <a href="tel:{{ \App\Models\Setting::get('contact_phone') }}" class="font-bold text-sea-700">{{ \App\Models\Setting::get('contact_phone') }}</a></p>
        <p class="mt-2 text-base text-stone-600">Lupa kata sandi? Admin bisa membuatkan yang baru.</p>
    </div>
</x-layouts.owner>
