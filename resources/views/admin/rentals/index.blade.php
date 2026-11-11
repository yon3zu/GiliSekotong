<x-layouts.admin title="Sewa Alat">
    <x-slot:actions><a href="{{ route('admin.rentals.create') }}" class="btn-primary btn-sm">+ Tambah alat</a></x-slot:actions>
    <div class="card max-w-3xl overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Urut</th><th>Alat</th><th class="text-right">Harga</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td class="num text-stone-400">{{ $item->sort }}</td>
                        <td><p class="font-semibold">{{ $item->name }}</p><p class="text-xs text-stone-500">{{ $item->description }}</p></td>
                        <td class="num text-right font-semibold whitespace-nowrap">{{ rupiah($item->price) }} <span class="font-normal text-stone-500">/pcs</span></td>
                        <td>@if ($item->is_active)<span class="chip tone-layak">Aktif</span>@else<span class="chip tone-none">Nonaktif</span>@endif</td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.rentals.edit', $item) }}" class="btn-ghost btn-sm">Ubah</a>
                            <form action="{{ route('admin.rentals.destroy', $item) }}" method="post" class="inline" onsubmit="return confirm('Hapus alat ini?')">@csrf @method('delete')<button class="btn-ghost btn-sm text-coral-700">Hapus</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="mt-3 max-w-3xl text-xs text-stone-500">Harga per pcs per trip. Pemesan bisa menyewa tiap alat paling banyak sejumlah penumpang.</p>
</x-layouts.admin>
