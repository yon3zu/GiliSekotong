{{-- Peringatan dini cuaca BMKG khusus Nusa Tenggara Barat. $alerts --}}
@forelse ($alerts as $alert)
    @php($tone = ['tidak_layak' => 'tidak', 'waspada' => 'waspada', 'info' => 'none'][$alert->impact])
    <article class="rounded-xl p-4 tone-{{ $tone }} {{ $alert->isActive() ? '' : 'opacity-60' }}">
        <div class="flex flex-wrap items-center gap-2">
            <x-icon name="alert" class="size-4"/>
            <p class="font-semibold">{{ $alert->title }}</p>
            <span class="chip bg-white/70">{{ $alert->impactLabel() }}</span>
            @unless ($alert->isActive())<span class="chip bg-white/70">Sudah berakhir</span>@endunless
        </div>
        @if ($alert->areas)<p class="mt-1.5 text-sm"><strong>Wilayah:</strong> {{ $alert->areas }}</p>@endif
        <p class="mt-1 text-xs">
            @if ($alert->starts_at){{ $alert->starts_at->translatedFormat('d M H:i') }}@endif
            @if ($alert->expires_at) – {{ $alert->expires_at->translatedFormat('d M H:i') }} WITA @endif
            @if ($alert->link) · <a href="{{ $alert->link }}" target="_blank" rel="noopener" class="font-semibold underline">detail BMKG</a>@endif
        </p>
    </article>
@empty
    <p class="flex items-center gap-2 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-900 ring-1 ring-emerald-200">
        <x-icon name="check" class="size-4 shrink-0"/> Tidak ada peringatan dini cuaca BMKG untuk Nusa Tenggara Barat saat ini.
    </p>
@endforelse
