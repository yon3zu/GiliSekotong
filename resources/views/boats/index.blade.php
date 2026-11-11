<x-layouts.app title="Daftar Perahu">
    <section class="border-b border-sand-200 bg-sand-100">
        <div class="container-x py-10 sm:py-14">
            <p class="eyebrow">Langkah 1 dari 3 · Pilih perahu</p>
            <h1 class="h-display mt-3 text-4xl sm:text-5xl">Perahu trip 3 gili</h1>
            <p class="mt-3 max-w-2xl text-stone-700">Semua perahu berangkat dari Dermaga Tawun dan bisa dipakai untuk trip 3 gili atau mancing. Bedanya ada di kapasitas (maksimal 12 orang) dan fasilitas. Harga dihitung per perahu.</p>

            <form method="get" class="mt-6 flex flex-wrap items-center gap-2">
                <label for="rombongan" class="text-sm font-semibold">Rombongan saya</label>
                <input id="rombongan" type="number" name="rombongan" min="1" max="12" value="{{ $group ?: '' }}" placeholder="mis. 9" class="input w-24 py-2">
                <span class="text-sm text-stone-600">orang</span>
                <button class="btn-sea btn-sm py-2">Saring</button>
                @if ($group)
                    <a href="{{ route('boats.index') }}" class="text-sm font-semibold text-coral-600 hover:underline">Hapus saringan</a>
                @endif
            </form>
        </div>
    </section>

    <section class="container-x py-10">
        <x-flash/>
        @if ($group)
            <p class="mb-6 text-sm text-stone-600">{{ $boats->count() }} perahu muat untuk <strong>{{ $group }} orang</strong>. Harga tetap per perahu.</p>
        @endif
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($boats as $boat)
                @include('boats._card', ['boat' => $boat])
            @empty
                <div class="card col-span-full p-10 text-center">
                    <p class="font-display text-xl font-semibold">Belum ada perahu yang cukup untuk {{ $group }} orang.</p>
                    <p class="mt-2 text-sm text-stone-600">Satu perahu paling banyak 12 orang. Rombongan lebih besar bisa dibagi ke dua perahu, hubungi kami lewat WhatsApp untuk diatur.</p>
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.app>
