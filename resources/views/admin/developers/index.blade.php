<x-layouts.admin title="Tim Pengembang">
    <x-slot:actions><a href="{{ route('developers') }}" target="_blank" class="btn-ghost btn-sm">Lihat halaman</a></x-slot:actions>

    <form action="{{ route('admin.developers.appearance') }}" method="post" enctype="multipart/form-data" class="card grid gap-6 p-6 lg:grid-cols-[2fr_1fr]">
        @csrf
        <div>
            <h2 class="font-display text-lg font-semibold">Foto banner</h2>
            <p class="mt-1 text-xs text-stone-500">Tampil lebar di bagian atas halaman, misalnya foto bersama tim. Pakai foto mendatar (landscape), JPG/PNG maks. 8 MB.</p>
            @if ($banner)
                <img src="{{ asset('storage/'.$banner) }}" alt="Banner saat ini" class="mt-3 aspect-[21/9] w-full rounded-xl object-cover">
                <label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="remove_banner" value="1" class="size-4 rounded border-sand-300 text-sea-600"> Hapus banner</label>
            @else
                <div class="mt-3 flex aspect-[21/9] w-full items-center justify-center rounded-xl border-2 border-dashed border-sand-300 text-sm text-stone-500">Belum ada banner</div>
            @endif
            <input type="file" name="banner" accept="image/jpeg,image/png" class="input mt-3">
            <x-error name="banner"/>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold">{{ \App\Models\Developer::LEAD['name'] }}</h2>
            <p class="mt-1 text-xs text-stone-500">{{ \App\Models\Developer::LEAD['tag'] }}. Nama dan tag tetap, selalu paling awal di barisan mahasiswa. Hanya fotonya yang bisa diganti.</p>
            @if ($leadPhoto)
                <img src="{{ asset('storage/'.$leadPhoto) }}" alt="Foto saat ini" class="mt-3 size-28 rounded-full object-cover object-top">
                <label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="remove_lead_photo" value="1" class="size-4 rounded border-sand-300 text-sea-600"> Hapus foto</label>
            @else
                <div class="mt-3 flex size-28 items-center justify-center rounded-full border-2 border-dashed border-sand-300 text-xs text-stone-500">Belum ada foto</div>
            @endif
            <input type="file" name="lead_photo" accept="image/jpeg,image/png" class="input mt-3">
            <x-error name="lead_photo"/>
        </div>
        <div class="lg:col-span-2"><button class="btn-primary">Simpan foto</button></div>
    </form>

    @foreach (['dosen' => $lecturers, 'mahasiswa' => $students] as $kind => $people)
        <section class="mt-8">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-display text-xl font-semibold">{{ \App\Models\Developer::KINDS[$kind] }}</h2>
                <a href="{{ route('admin.developers.create', ['jenis' => $kind]) }}" class="btn-primary btn-sm">+ Tambah {{ strtolower(\App\Models\Developer::KINDS[$kind]) }}</a>
            </div>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($people as $person)
                    <div class="card flex items-center gap-3 p-4">
                        @if ($person->photo)
                            <img src="{{ $person->photoUrl() }}" alt="" class="size-14 shrink-0 rounded-full object-cover object-top">
                        @else
                            <span class="flex size-14 shrink-0 items-center justify-center rounded-full bg-sea-50 text-sea-700"><x-icon name="users"/></span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-sea-950">{{ $person->name }}</p>
                            <p class="truncate text-xs text-stone-500">{{ $person->tag ?: 'Tanpa tag' }} · urutan {{ $person->sort }}@unless ($person->is_active) · <span class="font-semibold">disembunyikan</span>@endunless</p>
                            <div class="mt-2 flex gap-2">
                                <a href="{{ route('admin.developers.edit', $person) }}" class="btn-ghost btn-sm">Ubah</a>
                                <form action="{{ route('admin.developers.destroy', $person) }}" method="post" onsubmit="return confirm('Hapus {{ addslashes($person->name) }}?')">@csrf @method('delete')<button class="btn-ghost btn-sm text-coral-700">Hapus</button></form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-stone-500 sm:col-span-2">Belum ada {{ strtolower(\App\Models\Developer::KINDS[$kind]) }}{{ $kind === 'mahasiswa' ? ' lain' : '' }}.</p>
                @endforelse
            </div>
        </section>
    @endforeach
</x-layouts.admin>
