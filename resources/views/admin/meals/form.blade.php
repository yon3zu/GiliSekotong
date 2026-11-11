<x-layouts.admin :title="$meal->exists ? 'Ubah '.$meal->name : 'Tambah Paket Makan'">
    <form action="{{ $meal->exists ? route('admin.meals.update', $meal) : route('admin.meals.store') }}" method="post" class="card grid max-w-2xl gap-4 p-6 sm:grid-cols-2">
        @csrf
        @if ($meal->exists) @method('put') @endif
        <div><label class="label" for="code">Kode</label><input id="code" name="code" value="{{ old('code', $meal->code) }}" class="input" placeholder="A" required></div>
        <div><label class="label" for="name">Nama paket</label><input id="name" name="name" value="{{ old('name', $meal->name) }}" class="input" required></div>
        <div class="sm:col-span-2"><label class="label" for="price">Harga per paket (Rp)</label><input id="price" type="number" name="price" value="{{ old('price', $meal->price) }}" class="input" required></div>
        <div class="sm:col-span-2"><label class="label" for="menu">Menu <span class="font-normal text-stone-500">(satu per baris)</span></label><textarea id="menu" name="menu" rows="6" class="input" required>{{ old('menu', implode("\n", $meal->menu ?? [])) }}</textarea></div>
        <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $meal->is_active)) class="size-4 rounded border-sand-300 text-sea-600"> Bisa dipesan</label>
        <div class="flex gap-2 sm:col-span-2"><button class="btn-primary">Simpan</button><a href="{{ route('admin.meals.index') }}" class="btn-ghost">Batal</a></div>
    </form>
</x-layouts.admin>
