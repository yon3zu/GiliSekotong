@props(['light' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg viewBox="0 0 40 40" class="size-9 shrink-0" aria-hidden="true">
        <circle cx="20" cy="20" r="20" class="{{ $light ? 'fill-white/10' : 'fill-sea-800' }}"/>
        <path d="M20 7v19l9-3z" class="fill-coral-500"/>
        <path d="M18.5 10 11 24.5h7.5z" class="{{ $light ? 'fill-sand-100' : 'fill-sand-100' }}"/>
        <path d="M8 27.5h24l-3 4H11z" class="fill-sand-300"/>
        <path d="M6 33c2.5 0 2.5-1.5 5-1.5s2.5 1.5 5 1.5 2.5-1.5 5-1.5 2.5 1.5 5 1.5 2.5-1.5 5-1.5" fill="none" stroke-width="1.6" class="stroke-sea-200"/>
    </svg>
    <span class="leading-none">
        <span class="block font-display text-lg font-semibold tracking-tight {{ $light ? 'text-white' : 'text-sea-950' }}">Gili<span class="text-coral-500">Sekotong</span></span>
        <span class="block text-[10px] font-semibold tracking-[0.14em] whitespace-nowrap uppercase {{ $light ? 'text-sea-200' : 'text-sea-600' }}">gilisekotong.com</span>
    </span>
</span>
