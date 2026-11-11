<x-layouts.admin :title="$developer->exists ? 'Ubah '.$developer->name : 'Tambah Anggota Tim'">
    <form action="{{ $developer->exists ? route('admin.developers.update', $developer) : route('admin.developers.store') }}" method="post" enctype="multipart/form-data" class="card grid max-w-2xl gap-4 p-6 sm:grid-cols-2">
        @csrf
        @if ($developer->exists) @method('put') @endif
        <div class="sm:col-span-2"><label class="label" for="name">Nama lengkap (dengan gelar untuk dosen)</label><input id="name" name="name" value="{{ old('name', $developer->name) }}" class="input" required><x-error name="name"/></div>
        <div>
            <label class="label" for="kind">Kelompok</label>
            <select id="kind" name="kind" class="input">
                @foreach (\App\Models\Developer::KINDS as $value => $label)<option value="{{ $value }}" @selected(old('kind', $developer->kind) === $value)>{{ $label }}</option>@endforeach
            </select>
        </div>
        <div><label class="label" for="tag">Tag / peran <span class="font-normal text-stone-500">(opsional)</span></label><input id="tag" name="tag" value="{{ old('tag', $developer->tag) }}" class="input" placeholder="Dosen Pembimbing, UI Designer" maxlength="60"><x-error name="tag"/></div>
        <div><label class="label" for="sort">Urutan</label><input id="sort" type="number" min="0" max="999" name="sort" value="{{ old('sort', $developer->sort) }}" class="input" required><x-error name="sort"/></div>
        <div class="sm:col-span-2">
            <label class="label" for="photo">Foto <span class="font-normal text-stone-500">(opsional, JPG/PNG maks. 8 MB)</span></label>
            @if ($developer->photo)
                <div class="mb-2 flex items-center gap-3">
                    <img src="{{ $developer->photoUrl() }}" alt="" class="size-16 rounded-full object-cover object-top">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remove_photo" value="1" class="size-4 rounded border-sand-300 text-sea-600"> Hapus foto</label>
                </div>
            @endif
            <input id="photo" type="file" name="photo" accept="image/jpeg,image/png" class="input">
            <x-error name="photo"/>
        </div>
        <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $developer->is_active)) class="size-4 rounded border-sand-300 text-sea-600"> Tampilkan di halaman publik</label>
        <div class="flex gap-2 sm:col-span-2"><button class="btn-primary">Simpan</button><a href="{{ route('admin.developers.index') }}" class="btn-ghost">Batal</a></div>
    </form>
</x-layouts.admin>
