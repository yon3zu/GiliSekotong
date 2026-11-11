@props(['boat', 'class' => ''])
@if ($boat->photoUrl())
    <img src="{{ $boat->photoUrl() }}" alt="Foto {{ $boat->name }}" loading="lazy" {{ $attributes->merge(['class' => "object-cover $class"]) }}>
@else
    <x-boat-art :boat="$boat" :class="$class"/>
@endif
