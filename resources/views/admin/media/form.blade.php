<x-layouts.admin :title="$item->exists ? 'Ubah media' : 'Unggah foto / video'">
    <div class="grid gap-6 xl:grid-cols-[1fr_320px]">
        <form action="{{ $item->exists ? route('admin.media.update', $item) : route('admin.media.store') }}" method="post" enctype="multipart/form-data"
              class="card grid max-w-3xl gap-4 self-start p-6 sm:grid-cols-2"
              x-data="{ preview: null, isVideo: @js($item->isVideo()), pick(e) { const f = e.target.files[0]; if (!f) return; this.isVideo = f.type.startsWith('video/'); this.preview = URL.createObjectURL(f); } }">
            @csrf
            @if ($item->exists) @method('put') @endif

            @if ($item->exists)
                <div class="sm:col-span-2">
                    <span class="label">Media saat ini</span>
                    @if ($item->isVideo())
                        <video src="{{ $item->url() }}" poster="{{ $item->posterUrl() }}" controls muted class="aspect-video w-full rounded-xl bg-black object-cover"></video>
                    @else
                        <img src="{{ $item->url() }}" alt="{{ $item->title }}" class="aspect-video w-full rounded-xl object-cover">
                    @endif
                    <p class="mt-1 text-xs text-stone-500">Untuk mengganti file, hapus media ini lalu unggah yang baru.</p>
                </div>
            @else
                <div class="sm:col-span-2">
                    <label class="label" for="file">File foto atau video</label>
                    <label class="flex cursor-pointer flex-col items-center justify-center gap-2 overflow-hidden rounded-2xl border-2 border-dashed border-sand-300 p-4 text-center hover:border-sea-400">
                        <template x-if="preview && !isVideo"><img :src="preview" alt="" class="max-h-64 rounded-xl object-contain"></template>
                        <template x-if="preview && isVideo"><video :src="preview" muted autoplay loop playsinline class="max-h-64 rounded-xl"></video></template>
                        <template x-if="!preview"><span class="py-6 text-sm text-stone-600"><x-icon name="upload" class="mx-auto mb-2 size-7 text-sea-600"/>Pilih foto (JPG/PNG, maks. 8 MB) atau video (MP4/WebM/MOV, maks. 35 MB)</span></template>
                        <input id="file" type="file" name="file" accept="image/jpeg,image/png,video/mp4,video/webm,video/quicktime" required class="sr-only" @change="pick($event)">
                    </label>
                    <x-error name="file"/>
                </div>
            @endif

            <div class="sm:col-span-2" x-show="isVideo" x-cloak>
                <label class="label" for="poster">Gambar sampul video <span class="font-normal text-stone-500">(disarankan)</span></label>
                <input id="poster" type="file" name="poster" accept="image/jpeg,image/png" class="block w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-sand-100 file:px-3 file:py-1.5 file:font-semibold">
                <p class="mt-1 text-xs text-stone-500">Ditampilkan sebelum video dimuat dan di HP yang menghemat data. Bila kosong, sistem mencoba mengambil gambar dari detik pertama video.</p>
                <x-error name="poster"/>
            </div>

            <div class="sm:col-span-2">
                <label class="label" for="title">Keterangan</label>
                <input id="title" name="title" value="{{ old('title', $item->title) }}" maxlength="120" class="input" placeholder="mis. Air jernih dan karang dangkal di Gili Nanggu" required>
                <p class="mt-1 text-xs text-stone-500">Tampil di galeri dan dipakai sebagai teks alternatif gambar (dibaca Google dan pembaca layar).</p>
                <x-error name="title"/>
            </div>
            <div>
                <label class="label" for="location">Lokasi</label>
                <select id="location" name="location" class="input">
                    <option value="">Tanpa lokasi</option>
                    @foreach (\App\Models\MediaItem::LOCATIONS as $loc)<option @selected(old('location', $item->location) === $loc)>{{ $loc }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="label" for="placement">Tampil di</label>
                <select id="placement" name="placement" class="input">
                    @foreach (\App\Models\MediaItem::PLACEMENTS as $k => $v)<option value="{{ $k }}" @selected(old('placement', $item->placement) === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div><label class="label" for="sort">Urutan</label><input id="sort" type="number" name="sort" min="0" value="{{ old('sort', $item->sort) }}" class="input num"><p class="mt-1 text-xs text-stone-500">Angka kecil tampil lebih dulu.</p></div>
            <label class="flex items-center gap-2 self-center text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active)) class="size-4 rounded border-sand-300 text-sea-600"> Tampilkan di beranda</label>
            <div class="flex gap-2 sm:col-span-2"><button class="btn-primary">Simpan</button><a href="{{ route('admin.media.index') }}" class="btn-ghost">Batal</a></div>
        </form>

        <aside class="card space-y-3 self-start p-5 text-sm leading-relaxed text-stone-700">
            <h2 class="font-display text-lg font-semibold">Tips foto & video</h2>
            <ul class="list-inside list-disc space-y-1.5">
                <li><strong>Hero:</strong> foto mendatar (landscape), objek utama di tengah, langit tidak terlalu terang agar tulisan terbaca.</li>
                <li><strong>Galeri:</strong> campur pemandangan pulau, bawah laut, perahu, dan suasana makan di Gili Sudak.</li>
                <li><strong>Video:</strong> 10–20 detik, tanpa suara penting, 720p atau 1080p, ukuran di bawah 15 MB supaya cepat di HP.</li>
                <li>Foto otomatis diperkecil ke 2000 px dan data lokasi GPS kamera dihapus.</li>
                <li>Gunakan foto milik sendiri atau yang sudah ada izinnya.</li>
            </ul>
            <p class="text-xs text-stone-500">Memperkecil video gratis: buka di HandBrake atau aplikasi pemotong video di HP, pilih 720p, lalu ekspor ulang.</p>
        </aside>
    </div>
</x-layouts.admin>
