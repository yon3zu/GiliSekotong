{{-- Alasan aturan keselamatan BMKG untuk satu SeaCondition. $condition --}}
@if ($condition?->bmkg_reasons)
    <ul class="mt-2 space-y-1">
        @foreach ($condition->bmkg_reasons as $r)
            <li class="flex gap-1.5 text-[11px] leading-snug {{ $r['level'] === 'Tidak Layak' ? 'text-red-800' : 'text-amber-800' }}">
                <x-icon :name="$r['type'] === 'gempa' ? 'quake' : 'alert'" class="mt-px size-3.5 shrink-0"/>
                <span>{{ $r['text'] }}</span>
            </li>
        @endforeach
    </ul>
@endif
