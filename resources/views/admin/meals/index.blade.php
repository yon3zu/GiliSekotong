<x-layouts.admin title="Paket Makan">
    <x-slot:actions><a href="{{ route('admin.meals.create') }}" class="btn-primary btn-sm">+ Tambah paket</a></x-slot:actions>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($meals as $meal)
            <div class="card p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="flex size-10 items-center justify-center rounded-full bg-coral-500 font-display text-lg font-semibold text-white">{{ $meal->code }}</span>
                    @unless ($meal->is_active)<span class="chip tone-none">Nonaktif</span>@endunless
                </div>
                <p class="mt-3 font-display text-lg font-semibold">{{ $meal->name }}</p>
                <p class="num text-sm font-semibold text-coral-600">{{ rupiah($meal->price) }} / paket</p>
                <ul class="mt-3 list-inside list-disc text-sm text-stone-600">@foreach ($meal->menu as $item)<li>{{ $item }}</li>@endforeach</ul>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('admin.meals.edit', $meal) }}" class="btn-ghost btn-sm">Ubah</a>
                    <form action="{{ route('admin.meals.destroy', $meal) }}" method="post" onsubmit="return confirm('Hapus paket ini?')">@csrf @method('delete')<button class="btn-ghost btn-sm text-coral-700">Hapus</button></form>
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
