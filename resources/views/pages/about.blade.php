<x-layouts.app title="Tentang Kami">
    <section class="relative overflow-hidden bg-sea-900 text-white">
        <div class="container-x grid items-center gap-10 py-14 sm:py-20 lg:grid-cols-2">
            <div>
                <p class="eyebrow text-coral-400">Tentang kami</p>
                <h1 class="h-display mt-3 text-4xl text-white sm:text-5xl">Perahu nelayan, dikelola bersama warga Sekotong Barat.</h1>
                <p class="mt-5 max-w-lg text-lg leading-relaxed text-sea-100">
                    GiliSekotong berawal dari beberapa pemilik perahu di sekitar Dermaga Tawun yang biasa mengantar tamu ke gili setelah selesai melaut.
                    Platform ini dibuat agar tamu bisa memesan dengan harga yang jelas dan nakhoda mendapat jadwal yang teratur.
                </p>
            </div>
            <x-hero-art class="w-full"/>
        </div>
        <div class="wave-edge h-3"></div>
    </section>

    <section class="container-x grid gap-6 py-16 sm:grid-cols-3">
        <div class="card p-6"><p class="num font-display text-5xl font-semibold text-coral-500">{{ $boatCount }}</p><p class="mt-2 text-sm text-stone-600">perahu aktif, semuanya milik warga lokal</p></div>
        <div class="card p-6"><p class="num font-display text-5xl font-semibold text-coral-500">3</p><p class="mt-2 text-sm text-stone-600">gili dalam satu trip: Nanggu, Sudak, Kedis</p></div>
        <div class="card p-6"><p class="num font-display text-5xl font-semibold text-coral-500">1</p><p class="mt-2 text-sm text-stone-600">harga per perahu, berapa pun jumlah rombongannya</p></div>
    </section>

    <section class="container-x grid gap-12 lg:grid-cols-2">
        <div class="prose-id">
            <h2 class="h-display mb-4 text-3xl">Kenapa harga per perahu?</h2>
            <p>Bensin dan tenaga nakhoda sama saja, entah perahu membawa empat atau dua belas orang, entah dipakai keliling gili atau mancing. Jadi kami tidak menghitung per kepala. Rombongan besar otomatis lebih hemat per orang.</p>
            <p>Yang dihitung terpisah hanya yang memang Anda pilih sendiri: paket makan dari Kedai Bintang dan sewa alat seperti pelampung, kacamata snorkeling, atau fins.</p>
        </div>
        <div class="prose-id">
            <h2 class="h-display mb-4 text-3xl">Keselamatan yang bisa diukur</h2>
            <p>Dulu keputusan berlayar hanya berdasarkan pengalaman nakhoda. Pengalaman itu tetap jadi dasar, tetapi kini dicatat. Setiap catatan kondisi laut dan keputusan berangkat atau tidak disimpan sebagai data.</p>
            <p>Dari data tersebut sistem membangun <strong>pohon keputusan (algoritma C4.5)</strong> yang memberi rekomendasi harian: layak, waspada, atau tidak layak. Hasilnya ditampilkan terbuka di halaman <a href="{{ route('sea') }}" class="font-semibold text-sea-700 underline">Kondisi Laut &amp; Cuaca</a>.</p>
        </div>
    </section>

    <section class="container-x mt-16">
        <h2 class="h-display text-3xl">Yang kami pegang</h2>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach ([
                ['shield', 'Tidak memaksakan berlayar', 'Laut tidak layak berarti tanggal ditutup. Pesanan dijadwalkan ulang, tidak hangus.'],
                ['users', 'Uang berputar di desa', 'Pemilik perahu, nakhoda, dan penyedia paket makan semuanya warga sekitar Tawun.'],
                ['wave', 'Karang dijaga bersama', 'Tamu diberi briefing singkat soal snorkeling yang tidak merusak karang sebelum berangkat.'],
            ] as [$icon, $title, $text])
                <div class="rounded-2xl bg-sand-100 p-6">
                    <x-icon :name="$icon" class="size-7 text-coral-600"/>
                    <h3 class="mt-4 font-display text-lg font-semibold">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.app>
