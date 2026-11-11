@props(['name', 'tag' => null, 'photo' => null, 'lead' => false])
@php
    $words = preg_split('/\s+/', trim($name));
    $initials = mb_strtoupper(mb_substr($words[0], 0, 1).(count($words) > 1 ? mb_substr($words[1], 0, 1) : ''));
@endphp
<div @class(['card flex items-center gap-4 p-5', 'border-sea-800 bg-sea-900' => $lead])>
    @if ($photo)
        <img src="{{ $photo }}" alt="Foto {{ $name }}" loading="lazy" class="size-20 shrink-0 rounded-full object-cover object-top">
    @else
        <span @class(['flex size-20 shrink-0 items-center justify-center rounded-full font-display text-2xl font-semibold', 'bg-white/10 text-white' => $lead, 'bg-sea-50 text-sea-800' => ! $lead]) aria-hidden="true">{{ $initials }}</span>
    @endif
    <div class="min-w-0">
        <p @class(['font-display text-lg font-semibold break-words', 'text-white' => $lead, 'text-sea-950' => ! $lead])>{{ $name }}</p>
        @if ($tag)
            <p @class(['chip mt-2', 'bg-coral-500 text-white' => $lead, 'bg-sand-100 text-sea-900' => ! $lead])>{{ $tag }}</p>
        @endif
    </div>
</div>
