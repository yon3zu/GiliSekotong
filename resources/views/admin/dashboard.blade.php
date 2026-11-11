<x-layouts.admin title="Ringkasan">
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            ['Perlu verifikasi', $stats['review'], 'pembayaran', route('admin.bookings.index', ['status' => 'menunggu_verifikasi'])],
            ['Trip hari ini', $stats['today'], 'rombongan', route('admin.bookings.index', ['tanggal' => today()->toDateString()])],
            ['Trip bulan ini', $stats['month_trips'], 'terkonfirmasi', route('admin.reports.index')],
            ['Pendapatan bulan ini', rupiah($stats['month_revenue']), now()->translatedFormat('F Y'), route('admin.reports.index')],
        ] as [$label, $value, $hint, $url])
            <a href="{{ $url }}" class="card p-4 transition hover:border-sea-300 sm:p-5">
                <p class="text-xs font-semibold text-stone-500">{{ $label }}</p>
                <p class="num mt-2 font-display text-2xl font-semibold text-sea-950 sm:text-3xl">{{ $value }}</p>
                <p class="mt-1 text-xs text-stone-500">{{ $hint }}</p>
            </a>
        @endforeach
    </div>

    <section class="card mt-6 p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-display text-lg font-semibold">Kondisi laut 7 hari</h2>
            <a href="{{ route('admin.sea.index') }}" class="text-sm font-semibold text-sea-700 hover:underline">Kelola kondisi laut →</a>
        </div>
        <div class="mt-4 grid grid-cols-7 gap-2 overflow-x-auto">
            @for ($i = 0; $i < 7; $i++)
                @php($d = today()->addDays($i))
                @php($c = $conditions[$d->toDateString()] ?? null)
                @php($tone = \App\Support\Sailing::tone($c?->decision()))
                <div class="min-w-[4.5rem] rounded-xl p-2 text-center tone-{{ $tone }}">
                    <p class="text-[10px] font-semibold uppercase">{{ $d->translatedFormat('D') }}</p>
                    <p class="num font-display text-lg font-semibold">{{ $d->format('d') }}</p>
                    <p class="text-[10px] font-semibold leading-tight">{{ $c?->decision() ?? '—' }}</p>
                </div>
            @endfor
        </div>
        <p class="mt-3 text-xs text-stone-500">
            @if ($model)
                Model aktif: {{ $model->algorithm }} · akurasi uji {{ $model->accuracy }}% · dilatih {{ $model->created_at->diffForHumans() }}
            @else
                Belum ada model pohon keputusan aktif.
            @endif
        </p>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="card p-5">
            <h2 class="font-display text-lg font-semibold">Menunggu verifikasi pembayaran</h2>
            <div class="mt-3 divide-y divide-sand-100">
                @forelse ($needsReview as $b)
                    <a href="{{ route('admin.bookings.show', $b) }}" class="flex items-center justify-between gap-3 py-3 hover:bg-sand-50">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ $b->contact_name }} <span class="num font-mono text-xs font-normal text-stone-500">{{ $b->code }}</span></p>
                            <p class="text-xs text-stone-500">{{ $b->boat->name }} · {{ $b->trip_date->translatedFormat('d M') }} · dikirim {{ $b->paid_at?->diffForHumans() }}</p>
                        </div>
                        <span class="num shrink-0 text-sm font-semibold">{{ rupiah($b->transferAmount()) }}</span>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-stone-500">Tidak ada yang menunggu. Semua beres.</p>
                @endforelse
            </div>
        </section>

        <section class="card p-5">
            <h2 class="font-display text-lg font-semibold">Jadwal 7 hari ke depan</h2>
            <div class="mt-3 divide-y divide-sand-100">
                @forelse ($upcoming as $b)
                    @php($c = $conditions[$b->trip_date->toDateString()] ?? null)
                    <a href="{{ route('admin.bookings.show', $b) }}" class="flex items-center justify-between gap-3 py-3 hover:bg-sand-50">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ $b->trip_date->translatedFormat('D, d M') }} · {{ $b->departure_time }} — {{ $b->boat->name }}</p>
                            <p class="text-xs text-stone-500">{{ $b->contact_name }} · {{ $b->passengers }} org</p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <x-status :booking="$b"/>
                            @if ($c?->blocksBooking())<span class="chip tone-tidak">Laut tidak layak</span>@endif
                        </div>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-stone-500">Belum ada trip terjadwal.</p>
                @endforelse
                @foreach ($blocks as $bl)
                    <a href="{{ route('admin.blocks.index') }}" class="flex items-center justify-between gap-3 py-3 hover:bg-sand-50">
                        <div class="min-w-0"><p class="truncate text-sm font-semibold">{{ $bl->date->translatedFormat('D, d M') }} — {{ $bl->boat->name }}</p><p class="text-xs text-stone-500">{{ $bl->reasonLabel() }}{{ $bl->passengers ? ' · '.$bl->passengers.' org' : '' }}</p></div>
                        <span class="chip shrink-0 bg-coral-50 text-coral-700">Offline</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.admin>
