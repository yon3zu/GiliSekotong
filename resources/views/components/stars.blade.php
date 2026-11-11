@props(['value' => 0, 'class' => 'size-4'])
@php $filled = (int) round((float) $value); @endphp
<span {{ $attributes->merge(['class' => 'inline-flex']) }} role="img" aria-label="{{ number_format((float) $value, 1, ',', '') }} dari 5 bintang">
    @foreach (range(1, 5) as $n)
        <svg @class([$class, 'text-amber-500' => $n <= $filled, 'text-sand-300' => $n > $filled]) viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9z"/></svg>
    @endforeach
</span>
