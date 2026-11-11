<x-layouts.admin :title="$boat->exists ? 'Ubah '.$boat->name : 'Tambah Perahu'">
    <form action="{{ $boat->exists ? route('admin.boats.update', $boat) : route('admin.boats.store') }}" method="post" enctype="multipart/form-data" class="card grid max-w-4xl gap-4 p-6 sm:grid-cols-2">
        @csrf
        @if ($boat->exists) @method('put') @endif
        <div><label class="label" for="name">Nama perahu</label><input id="name" name="name" value="{{ old('name', $boat->name) }}" class="input" required></div>
        <div class="sm:col-span-2">
            <label class="label" for="owner_id">Pemilik kapal</label>
            <select id="owner_id" name="owner_id" class="input">
                <option value="">— Belum ada —</option>
                @foreach (\App\Models\User::query()->where('role', 'pemilik')->orderBy('name')->get() as $o)<option value="{{ $o->id }}" @selected(old('owner_id', $boat->owner_id) == $o->id)>{{ $o->name }} ({{ $o->phone }})</option>@endforeach
            </select>
            <p class="mt-1 text-xs text-stone-500">Akun pemilik dibuat di menu <a class="underline" href="{{ route('admin.owners.create') }}">Pemilik Kapal</a>.</p>
        </div>
        <div><label class="label" for="type">Jenis</label><input id="type" name="type" value="{{ old('type', $boat->type) }}" class="input" required></div>
        <div><label class="label" for="capacity">Kapasitas (maks. {{ \App\Models\Boat::MAX_CAPACITY }} orang)</label><input id="capacity" type="number" min="1" max="{{ \App\Models\Boat::MAX_CAPACITY }}" name="capacity" value="{{ old('capacity', $boat->capacity) }}" class="input" required></div>
        <div><label class="label" for="trip_price">Harga sewa / perahu (Rp)</label><input id="trip_price" type="number" name="trip_price" value="{{ old('trip_price', $boat->trip_price) }}" class="input" required></div>
        <div><label class="label" for="length_m">Panjang (m)</label><input id="length_m" type="number" step="0.1" name="length_m" value="{{ old('length_m', $boat->length_m) }}" class="input"></div>
        <div><label class="label" for="engine">Mesin</label><input id="engine" name="engine" value="{{ old('engine', $boat->engine) }}" class="input" placeholder="2 × Yamaha 25 PK"></div>
        <div><label class="label" for="captain">Nakhoda</label><input id="captain" name="captain" value="{{ old('captain', $boat->captain) }}" class="input"></div>
        <div><label class="label" for="built_year">Tahun pembuatan</label><input id="built_year" type="number" name="built_year" value="{{ old('built_year', $boat->built_year) }}" class="input"></div>
        <div class="sm:col-span-2"><label class="label" for="description">Deskripsi</label><textarea id="description" name="description" rows="3" class="input">{{ old('description', $boat->description) }}</textarea></div>
        <div><label class="label" for="facilities">Fasilitas <span class="font-normal text-stone-500">(satu per baris)</span></label><textarea id="facilities" name="facilities" rows="6" class="input">{{ old('facilities', implode("\n", $boat->facilities ?? [])) }}</textarea></div>
        <div>
            <label class="label" for="photo">Foto perahu</label>
            @if ($boat->photo)<img src="{{ $boat->photoUrl() }}" alt="" class="mb-2 aspect-video w-full rounded-xl object-cover">@endif
            <input id="photo" type="file" name="photo" accept="image/*" class="block w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-sand-100 file:px-3 file:py-1.5 file:font-semibold">
            <p class="mt-1 text-xs text-stone-500">Tanpa foto, ditampilkan ilustrasi perahu.</p>
            <label class="mt-4 flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $boat->is_active)) class="size-4 rounded border-sand-300 text-sea-600"> Tampilkan & bisa dipesan</label>
        </div>
        <div class="flex gap-2 sm:col-span-2"><button class="btn-primary">Simpan</button><a href="{{ route('admin.boats.index') }}" class="btn-ghost">Batal</a></div>
    </form>
</x-layouts.admin>
