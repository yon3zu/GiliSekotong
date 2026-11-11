<x-layouts.admin title="Tamu Offline & Kapal Ditutup">
    <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
        <section>
            <div class="card overflow-x-auto">
                <table class="table-base">
                    <thead><tr><th>Tanggal</th><th>Kapal</th><th>Alasan</th><th>Dilaporkan</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($upcoming as $b)
                            <tr @class(['bg-coral-50/50' => $b->created_at->gt(now()->subDay())])>
                                <td class="whitespace-nowrap"><p class="font-semibold">{{ $b->date->translatedFormat('D, d M Y') }}</p>@if ($b->created_at->gt(now()->subDay()))<span class="chip bg-coral-500 text-white">Baru</span>@endif</td>
                                <td><p class="font-semibold">{{ $b->boat->name }}</p><p class="text-xs text-stone-500">Pemilik: {{ $b->boat->owner?->name ?? '—' }}</p></td>
                                <td><p>{{ $b->reasonLabel() }}</p><p class="text-xs text-stone-500">{{ collect([$b->passengers ? $b->passengers.' org' : null, $b->amount !== null ? rupiah($b->amount) : null, $b->note])->filter()->join(' · ') }}</p></td>
                                <td class="text-xs">{{ $b->reporter?->name ?? '—' }}<br><span class="text-stone-500">{{ $b->created_at->diffForHumans() }}</span></td>
                                <td class="text-right"><form action="{{ route('admin.blocks.cancel', $b) }}" method="post" onsubmit="return confirm('Buka lagi tanggal ini untuk pemesanan online?')">@csrf<button class="btn-ghost btn-sm">Buka lagi</button></form></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-10 text-center text-stone-500">Tidak ada kapal yang ditutup ke depan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($recent->isNotEmpty())
                <h2 class="mt-6 mb-2 font-display text-lg font-semibold">Riwayat</h2>
                <ul class="card divide-y divide-sand-100 text-sm">
                    @foreach ($recent as $b)
                        <li class="flex justify-between gap-3 px-4 py-2.5">
                            <span>{{ $b->date->translatedFormat('d M Y') }} · {{ $b->boat->name }} · {{ $b->reasonLabel() }}</span>
                            <span class="text-xs text-stone-500">{{ $b->cancelled_at ? 'dibatalkan' : 'selesai' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <form action="{{ route('admin.blocks.store') }}" method="post" class="card space-y-3 self-start p-5">
            @csrf
            <h2 class="font-display text-lg font-semibold">Catat atas nama pemilik</h2>
            <p class="text-xs text-stone-500">Untuk pemilik yang melapor lewat telepon/WA.</p>
            <div><label class="label" for="boat_id">Kapal</label><select id="boat_id" name="boat_id" class="input">@foreach ($boats as $boat)<option value="{{ $boat->id }}" @selected(old('boat_id') == $boat->id)>{{ $boat->name }}{{ $boat->owner ? ' — '.$boat->owner->name : '' }}</option>@endforeach</select></div>
            <div><label class="label" for="date">Tanggal</label><input id="date" type="date" name="date" value="{{ old('date', today()->toDateString()) }}" min="{{ today()->toDateString() }}" class="input" required></div>
            <div><label class="label" for="reason">Alasan</label><select id="reason" name="reason" class="input">@foreach (\App\Models\BoatBlock::REASONS as $k => $v)<option value="{{ $k }}" @selected(old('reason') === $k)>{{ $v }}</option>@endforeach</select></div>
            <div class="grid grid-cols-2 gap-2">
                <div><label class="label" for="passengers">Jumlah tamu</label><input id="passengers" type="number" name="passengers" value="{{ old('passengers') }}" class="input"></div>
                <div><label class="label" for="amount">Uang (Rp)</label><input id="amount" type="number" name="amount" value="{{ old('amount') }}" class="input"></div>
            </div>
            <div><label class="label" for="note">Catatan</label><input id="note" name="note" value="{{ old('note') }}" class="input"></div>
            <button class="btn-primary w-full">Tutup tanggal ini</button>
        </form>
    </div>
</x-layouts.admin>
