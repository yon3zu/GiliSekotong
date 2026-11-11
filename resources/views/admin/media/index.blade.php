<x-layouts.admin title="Galeri Beranda">
    <x-slot:actions><a href="{{ route('admin.media.create') }}" class="btn-primary btn-sm">+ Unggah foto / video</a></x-slot:actions>

    <section>
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div>
                <h2 class="font-display text-lg font-semibold">Hero beranda</h2>
                <p class="text-sm text-stone-600">Tampil layar penuh paling atas. Lebih dari satu foto akan bergantian otomatis; video diputar tanpa suara.</p>
            </div>
            <a href="{{ route('admin.media.create', ['posisi' => 'hero']) }}" class="text-sm font-semibold text-sea-700 hover:underline">+ Tambah ke hero</a>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @forelse ($hero as $item)
                @include('admin.media._card')
            @empty
                <div class="rounded-2xl border-2 border-dashed border-sand-300 p-6 text-sm text-stone-600 sm:col-span-2 xl:col-span-4">
                    Belum ada media hero. Beranda memakai ilustrasi tiga gili sampai foto/video diunggah.
                </div>
            @endforelse
        </div>
    </section>

    <section class="mt-10">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div>
                <h2 class="font-display text-lg font-semibold">Galeri "Keindahan 3 Gili"</h2>
                <p class="text-sm text-stone-600">Tampil tepat di bawah hero. Disarankan 6–9 foto; pengunjung bisa mengeklik untuk memperbesar.</p>
            </div>
            <a href="{{ route('admin.media.create') }}" class="text-sm font-semibold text-sea-700 hover:underline">+ Tambah ke galeri</a>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @forelse ($gallery as $item)
                @include('admin.media._card')
            @empty
                <div class="rounded-2xl border-2 border-dashed border-sand-300 p-6 text-sm text-stone-600 sm:col-span-2 xl:col-span-4">
                    Belum ada foto galeri. Beranda menampilkan ilustrasi Gili Nanggu, Sudak, dan Kedis sebagai pengganti sementara.
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.admin>
