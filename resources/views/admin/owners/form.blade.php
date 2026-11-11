<x-layouts.admin :title="$owner->exists ? 'Ubah '.$owner->name : 'Daftarkan Pemilik Kapal'">
    <form action="{{ $owner->exists ? route('admin.owners.update', $owner) : route('admin.owners.store') }}" method="post" class="card grid max-w-2xl gap-4 p-6 sm:grid-cols-2">
        @csrf
        @if ($owner->exists) @method('put') @endif
        <div class="sm:col-span-2"><label class="label" for="name">Nama pemilik</label><input id="name" name="name" value="{{ old('name', $owner->name) }}" class="input" required><x-error name="name"/></div>
        <div>
            <label class="label" for="phone">Nomor HP <span class="font-normal text-stone-500">(dipakai untuk login)</span></label>
            <input id="phone" name="phone" inputmode="tel" value="{{ old('phone', $owner->phone) }}" class="input" placeholder="081234567890" required><x-error name="phone"/>
        </div>
        <div>
            <label class="label" for="password">Kata sandi {{ $owner->exists ? '(kosongkan bila tetap)' : '' }}</label>
            <input id="password" name="password" type="text" autocomplete="off" class="input" @required(! $owner->exists) placeholder="min. 6 karakter, mudah diingat"><x-error name="password"/>
        </div>
        <div class="sm:col-span-2"><label class="label" for="email">Email <span class="font-normal text-stone-500">(opsional)</span></label><input id="email" type="email" name="email" value="{{ old('email', $owner->email) }}" class="input"><x-error name="email"/></div>
        <fieldset class="sm:col-span-2">
            <legend class="label">Kapal milik orang ini</legend>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($boats as $boat)
                    @php($taken = $boat->owner_id && $boat->owner_id !== $owner->id)
                    <label class="flex items-center gap-2 rounded-xl border border-sand-200 p-3 text-sm has-[:checked]:border-sea-400 has-[:checked]:bg-sea-50">
                        <input type="checkbox" name="boats[]" value="{{ $boat->id }}" @checked(in_array($boat->id, old('boats', $owner->exists ? $owner->boats->pluck('id')->all() : []))) class="size-4 rounded border-sand-300 text-sea-600">
                        <span>{{ $boat->name }} @if ($taken)<span class="block text-xs text-amber-700">saat ini milik {{ $boat->owner?->name }}</span>@endif</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
        <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $owner->is_active ?? true)) class="size-4 rounded border-sand-300 text-sea-600"> Akun aktif (bisa login)</label>
        <p class="rounded-xl bg-sand-50 p-3 text-xs text-stone-600 sm:col-span-2">Catat nomor HP & kata sandi lalu berikan ke pemilik. Kata sandi sengaja ditampilkan (bukan titik-titik) supaya mudah dibacakan saat pelatihan.</p>
        <div class="flex gap-2 sm:col-span-2"><button class="btn-primary">Simpan</button><a href="{{ route('admin.owners.index') }}" class="btn-ghost">Batal</a></div>
    </form>
</x-layouts.admin>
