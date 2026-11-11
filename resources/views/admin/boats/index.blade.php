<x-layouts.admin title="Perahu">
    <x-slot:actions><a href="{{ route('admin.boats.create') }}" class="btn-primary btn-sm">+ Tambah perahu</a></x-slot:actions>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($boats as $boat)
            <div class="card overflow-hidden">
                <x-boat-photo :boat="$boat" class="aspect-[16/9] w-full"/>
                <div class="p-4">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-display text-lg font-semibold">{{ $boat->name }}</p>
                        @unless ($boat->is_active)<span class="chip tone-none">Nonaktif</span>@endunless
                    </div>
                    <p class="text-sm text-stone-600">{{ $boat->capacity }} orang · {{ $boat->engine }} · {{ rupiah($boat->trip_price) }}</p>
                    <p class="text-xs text-stone-500">Pemilik: {{ $boat->owner?->name ?? 'belum ada' }} · {{ $boat->bookings_count }} pesanan</p>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('admin.boats.edit', $boat) }}" class="btn-ghost btn-sm">Ubah</a>
                        <form action="{{ route('admin.boats.destroy', $boat) }}" method="post" onsubmit="return confirm('Hapus perahu ini?')">@csrf @method('delete')<button class="btn-ghost btn-sm text-coral-700">Hapus</button></form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
