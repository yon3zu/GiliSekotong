<x-layouts.admin title="Data BMKG">
    <x-slot:actions>
        <form action="{{ route('admin.bmkg.sync') }}" method="post">@csrf<button class="btn-sea btn-sm">Perbarui sekarang</button></form>
    </x-slot:actions>

    <section class="card overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Sumber</th><th>Jeda minimum</th><th>Sukses terakhir</th><th>Percobaan terakhir</th><th>Status</th></tr></thead>
            <tbody>
                @foreach (config('bmkg.sources') as $key => $src)
                    @php($s = $syncs[$key] ?? null)
                    <tr>
                        <td><p class="font-semibold">{{ $src['label'] }}</p><p class="max-w-72 truncate text-xs text-stone-500">{{ $src['url'] }}</p></td>
                        <td class="num">{{ $src['interval'] }} menit</td>
                        <td class="text-sm">{{ $s?->last_success_at?->diffForHumans() ?? '—' }}</td>
                        <td class="text-sm">{{ $s?->last_attempt_at?->diffForHumans() ?? '—' }}</td>
                        <td>
                            @if (! $s)<span class="chip tone-none">Belum pernah</span>
                            @elseif ($s->status === 'ok')<span class="chip tone-layak">OK</span>
                            @else<span class="chip tone-tidak">Gagal</span>@endif
                            <p class="mt-1 max-w-60 text-xs text-stone-500">{{ $s?->message }}{{ $due[$key] ? ' · siap diperbarui' : '' }}</p>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
    <p class="mt-2 text-xs text-stone-500">
        Halaman web hanya membaca database. Request ke BMKG terjadi lewat jadwal <code class="rounded bg-sand-100 px-1">php artisan schedule:work</code>,
        perintah <code class="rounded bg-sand-100 px-1">php artisan bmkg:sync</code>, tombol di atas, atau otomatis setelah pengunjung membuka halaman bila jeda minimum sudah lewat.
        Setelah gagal (mis. HTTP 429) sistem menunggu {{ config('bmkg.retry_after_failure') }} menit sebelum mencoba lagi.
    </p>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="card p-5">
            <h2 class="font-display text-lg font-semibold">Peringatan dini NTB (7 hari)</h2>
            <div class="mt-3 space-y-3">@include('bmkg._alerts', ['alerts' => $feed->recentAlerts()])</div>
        </section>
        <section class="card p-5">
            <h2 class="font-display text-lg font-semibold">15 gempa dirasakan terbaru</h2>
            <p class="text-xs text-stone-500">Baris tebal = Lombok & sekitarnya (≤ {{ config('bmkg.quake_radius_km') }} km dari Tawun / menyebut Lombok-NTB), ikut aturan keselamatan.</p>
            <div class="mt-3 overflow-x-auto">
                <table class="table-base num text-xs">
                    <thead><tr><th>Waktu (WITA)</th><th>M</th><th>Wilayah</th><th class="text-right">Jarak</th></tr></thead>
                    <tbody>
                        @foreach ($feed->latestQuakes() as $q)
                            <tr @class(['bg-amber-50 font-semibold' => $q->is_nearby, 'text-stone-500' => ! $q->is_nearby])>
                                <td class="whitespace-nowrap">{{ $q->occurred_at->format('d/m H:i') }}</td>
                                <td>{{ number_format($q->magnitude, 1) }}</td>
                                <td>{{ $q->region }}</td>
                                <td class="text-right whitespace-nowrap">{{ $q->distance_km }} km</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="card mt-6 p-5">
        <h2 class="font-display text-lg font-semibold">Prakiraan cuaca {{ config('bmkg.location.name') }}</h2>
        <div class="mt-4">@include('bmkg._forecast', ['days' => $feed->forecastByDay()])</div>
    </section>
</x-layouts.admin>
