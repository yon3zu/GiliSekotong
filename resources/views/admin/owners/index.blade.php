<x-layouts.admin title="Pemilik Kapal">
    <x-slot:actions><a href="{{ route('admin.owners.create') }}" class="btn-primary btn-sm">+ Daftarkan pemilik</a></x-slot:actions>

    @if ($unowned->isNotEmpty())
        <p class="mb-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200">
            Kapal belum punya pemilik: <strong>{{ $unowned->pluck('name')->join(', ') }}</strong>.
        </p>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($owners as $owner)
            <div @class(['card p-5', 'opacity-60' => ! $owner->is_active])>
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-display text-lg font-semibold">{{ $owner->name }}</p>
                        <p class="num text-sm text-stone-600">{{ $owner->phone }}</p>
                    </div>
                    @unless ($owner->is_active)<span class="chip tone-none">Nonaktif</span>@endunless
                </div>
                <p class="mt-3 text-xs font-semibold text-stone-500 uppercase">Kapal</p>
                <p class="text-sm">{{ $owner->boats->pluck('name')->join(', ') ?: 'Belum ada' }}</p>
                <a href="{{ route('admin.owners.edit', $owner) }}" class="btn-ghost btn-sm mt-4">Ubah / ganti sandi</a>
            </div>
        @empty
            <div class="card p-8 text-center text-stone-500 sm:col-span-2 xl:col-span-3">Belum ada pemilik kapal terdaftar.</div>
        @endforelse
    </div>
    <p class="mt-4 text-xs text-stone-500">Pemilik masuk di halaman <strong>/masuk</strong> memakai nomor HP + kata sandi, lalu langsung diarahkan ke halaman pemilik.</p>
</x-layouts.admin>
