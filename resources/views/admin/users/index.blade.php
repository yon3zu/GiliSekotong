<x-layouts.admin title="Pengguna">
    <x-slot:actions><a href="{{ route('admin.users.create') }}" class="btn-primary btn-sm">+ Tambah pengguna</a></x-slot:actions>
    <form method="get" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-48 flex-1"><label class="label" for="q">Cari</label><input id="q" name="q" value="{{ request('q') }}" class="input" placeholder="Nama atau email"></div>
        <div><label class="label" for="role">Peran</label><select id="role" name="role" class="input"><option value="">Semua</option>@foreach (\App\Models\User::ROLES as $k => $v)<option value="{{ $k }}" @selected(request('role') === $k)>{{ $v }}</option>@endforeach</select></div>
        <button class="btn-sea">Terapkan</button>
    </form>
    <div class="card overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Nama</th><th>Email</th><th>WhatsApp</th><th>Peran</th><th class="text-right">Pesanan</th><th></th></tr></thead>
            <tbody>
                @foreach ($users as $u)
                    <tr @class(['opacity-50' => ! $u->is_active])>
                        <td class="font-semibold">{{ $u->name }} @unless ($u->is_active)<span class="chip tone-none">Nonaktif</span>@endunless</td>
                        <td>{{ $u->email }}</td><td>{{ $u->phone ?? '—' }}</td>
                        <td><span @class(['chip', 'bg-coral-50 text-coral-700' => $u->role === 'superadmin', 'bg-sea-50 text-sea-800' => $u->role === 'admin', 'bg-sand-100 text-stone-700' => $u->role === 'pemesan'])>{{ $u->roleLabel() }}</span></td>
                        <td class="num text-right">{{ $u->bookings_count }}</td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.users.edit', $u) }}" class="btn-ghost btn-sm">Ubah</a>
                            @unless ($u->is(auth()->user()))
                                <form action="{{ route('admin.users.destroy', $u) }}" method="post" class="inline" onsubmit="return confirm('Hapus pengguna ini?')">@csrf @method('delete')<button class="btn-ghost btn-sm text-coral-700">Hapus</button></form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.admin>
