<x-layouts.owner title="Lapor Tamu Offline">
    <p class="mb-5 text-lg text-stone-700">Isi ini kalau kapal Anda sudah dipakai tamu yang pesan langsung (tidak lewat website). Tanggal itu akan ditutup supaya tidak dipesan orang lain.</p>

    @if ($errors->any())
        <div class="mb-5 rounded-2xl bg-red-600 p-4 text-lg font-semibold text-white">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('owner.report.store') }}" method="post" class="space-y-7" x-data="{ date: @js(old('date', today()->toDateString())) }">
        @csrf

        {{-- 1. Kapal --}}
        <fieldset>
            <legend class="mb-2 text-xl font-bold">1. Kapal yang mana?</legend>
            <div class="grid gap-3">
                @foreach ($boats as $boat)
                    <label class="flex cursor-pointer items-center gap-4 rounded-2xl bg-white p-4 text-lg font-semibold ring-2 ring-sand-200 has-[:checked]:bg-sea-50 has-[:checked]:ring-sea-600">
                        <input type="radio" name="boat_id" value="{{ $boat->id }}" @checked(old('boat_id', $boats->count() === 1 ? $boat->id : null) == $boat->id) class="size-6 text-sea-600">
                        {{ $boat->name }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        {{-- 2. Tanggal --}}
        <fieldset>
            <legend class="mb-2 text-xl font-bold">2. Tanggal berapa?</legend>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="date = @js(today()->toDateString())" class="rounded-2xl p-4 text-lg font-bold ring-2" :class="date === @js(today()->toDateString()) ? 'bg-sea-700 text-white ring-sea-700' : 'bg-white ring-sand-200'">Hari ini<br><span class="text-base font-normal">{{ today()->translatedFormat('d M') }}</span></button>
                <button type="button" @click="date = @js(today()->addDay()->toDateString())" class="rounded-2xl p-4 text-lg font-bold ring-2" :class="date === @js(today()->addDay()->toDateString()) ? 'bg-sea-700 text-white ring-sea-700' : 'bg-white ring-sand-200'">Besok<br><span class="text-base font-normal">{{ today()->addDay()->translatedFormat('d M') }}</span></button>
            </div>
            <label class="mt-3 block text-base text-stone-600">Atau pilih tanggal lain:
                <input type="date" name="date" x-model="date" min="{{ today()->toDateString() }}" class="input mt-1 py-3 text-lg">
            </label>
        </fieldset>

        {{-- 3. Alasan --}}
        <fieldset>
            <legend class="mb-2 text-xl font-bold">3. Kenapa?</legend>
            <div class="grid gap-3">
                @foreach (\App\Models\BoatBlock::REASONS as $value => $label)
                    <label class="flex cursor-pointer items-center gap-4 rounded-2xl bg-white p-4 text-lg font-semibold ring-2 ring-sand-200 has-[:checked]:bg-sea-50 has-[:checked]:ring-sea-600">
                        <input type="radio" name="reason" value="{{ $value }}" @checked(old('reason', 'tamu_offline') === $value) class="size-6 text-sea-600">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        {{-- 4. Opsional --}}
        <details class="rounded-2xl bg-white p-4 ring-1 ring-sand-200" @if (old('passengers') || old('amount')) open @endif>
            <summary class="cursor-pointer text-lg font-semibold">Tambah keterangan <span class="font-normal text-stone-500">(boleh dilewati)</span></summary>
            <div class="mt-4 space-y-4">
                <label class="block text-base font-semibold">Jumlah tamu
                    <input type="number" name="passengers" inputmode="numeric" min="1" value="{{ old('passengers') }}" class="input mt-1 py-3 text-lg" placeholder="mis. 8">
                </label>
                <label class="block text-base font-semibold">Uang yang diterima (Rp)
                    <input type="number" name="amount" inputmode="numeric" min="0" step="1000" value="{{ old('amount') }}" class="input mt-1 py-3 text-lg" placeholder="mis. 350000">
                    <span class="mt-1 block text-sm font-normal text-stone-500">Untuk catatan pembukuan Anda.</span>
                </label>
                <label class="block text-base font-semibold">Catatan
                    <input name="note" value="{{ old('note') }}" class="input mt-1 py-3 text-lg" placeholder="mis. rombongan keluarga Pak Amir">
                </label>
            </div>
        </details>

        <button class="w-full rounded-2xl bg-coral-500 py-5 text-xl font-bold text-white shadow-[0_6px_0_-2px_var(--color-coral-700)] active:translate-y-0.5">Kirim laporan</button>
    </form>
</x-layouts.owner>
