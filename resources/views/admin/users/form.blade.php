<x-layouts.admin :title="$user->exists ? 'Ubah '.$user->name : 'Tambah Pengguna'">
    <form action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" method="post" class="card grid max-w-2xl gap-4 p-6 sm:grid-cols-2">
        @csrf
        @if ($user->exists) @method('put') @endif
        <div class="sm:col-span-2"><label class="label" for="name">Nama</label><input id="name" name="name" value="{{ old('name', $user->name) }}" class="input" required></div>
        <div><label class="label" for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required></div>
        <div><label class="label" for="phone">WhatsApp</label><input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="input"></div>
        <div>
            <label class="label" for="role">Peran</label>
            <select id="role" name="role" class="input" @disabled($user->is(auth()->user()))>@foreach (\App\Models\User::ROLES as $k => $v)<option value="{{ $k }}" @selected(old('role', $user->role) === $k)>{{ $v }}</option>@endforeach</select>
            @if ($user->is(auth()->user()))<input type="hidden" name="role" value="{{ $user->role }}">@endif
        </div>
        <div><label class="label" for="password">Kata sandi {{ $user->exists ? '(kosongkan bila tetap)' : '' }}</label><input id="password" type="password" name="password" class="input" @required(! $user->exists) autocomplete="new-password"></div>
        <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="size-4 rounded border-sand-300 text-sea-600"> Akun aktif (bisa login)</label>
        <div class="rounded-xl bg-sand-50 p-3 text-xs leading-relaxed text-stone-600 sm:col-span-2">
            <strong>Super Admin</strong>: semua menu termasuk master data, pengguna, data latih, dan melatih model.
            <strong>Admin</strong>: pemesanan, verifikasi pembayaran, check-in, kondisi laut, melihat pohon keputusan, laporan.
            <strong>Pemilik Kapal</strong>: login dengan nomor HP, melihat jadwal, pembukuan &amp; penumpang kapalnya, serta melapor tamu offline. Lebih mudah dibuat lewat menu Pemilik Kapal.
            <strong>Pemesan</strong>: memesan dan mengelola pesanan sendiri.
        </div>
        <div class="flex gap-2 sm:col-span-2"><button class="btn-primary">Simpan</button><a href="{{ route('admin.users.index') }}" class="btn-ghost">Batal</a></div>
    </form>
</x-layouts.admin>
