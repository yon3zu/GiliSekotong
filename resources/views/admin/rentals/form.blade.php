<x-layouts.admin :title="$item->exists ? 'Ubah '.$item->name : 'Tambah Alat Sewa'">
    <form action="{{ $item->exists ? route('admin.rentals.update', $item) : route('admin.rentals.store') }}" method="post" class="card grid max-w-2xl gap-4 p-6 sm:grid-cols-2">
        @csrf
        @if ($item->exists) @method('put') @endif
        <div class="sm:col-span-2"><label class="label" for="name">Nama alat</label><input id="name" name="name" value="{{ old('name', $item->name) }}" class="input" placeholder="Kacamata snorkeling" required></div>
        <div class="sm:col-span-2"><label class="label" for="description">Keterangan singkat</label><input id="description" name="description" value="{{ old('description', $item->description) }}" class="input"></div>
        <div><label class="label" for="price">Harga per pcs (Rp)</label><input id="price" type="number" name="price" value="{{ old('price', $item->price) }}" class="input" required></div>
        <div><label class="label" for="sort">Urutan tampil</label><input id="sort" type="number" name="sort" value="{{ old('sort', $item->sort) }}" class="input" required></div>
        <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active)) class="size-4 rounded border-sand-300 text-sea-600"> Bisa disewa</label>
        <div class="flex gap-2 sm:col-span-2"><button class="btn-primary">Simpan</button><a href="{{ route('admin.rentals.index') }}" class="btn-ghost">Batal</a></div>
    </form>
</x-layouts.admin>
