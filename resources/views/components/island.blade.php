@props(['size' => 1, 'palm' => true, 'tone' => '#246b4a'])
<svg viewBox="0 0 320 180" {{ $attributes->merge(['class' => 'block']) }} aria-hidden="true">
    <rect width="320" height="180" fill="#cfeceb"/>
    <circle cx="260" cy="44" r="20" fill="#f6c76b" opacity=".9"/>
    <rect y="112" width="320" height="68" fill="#188c8a"/>
    <g transform="translate(160 114) scale({{ $size }})">
        <ellipse cx="0" cy="2" rx="104" ry="13" fill="#ebdfc8"/>
        <path d="M-86 0c18-30 50-46 86-46s70 16 86 46z" fill="{{ $tone }}"/>
        @if ($palm)
            <path d="M34-30c2-16 0-28-6-38" stroke="#5b3a21" stroke-width="3" fill="none"/>
            <path d="M28-68c-12-4-24 0-30 8 10-4 20-4 28-2-8-8-18-10-26-8 10-6 24-2 28 2 0-10 8-18 18-18-8 6-10 12-10 18 8-8 18-8 26-2-10 0-18 2-22 8 10 0 18 4 20 12-8-8-18-10-26-8" fill="#2f8a5f"/>
        @endif
    </g>
    <path d="M0 150c20 0 20-5 40-5s20 5 40 5 20-5 40-5 20 5 40 5 20-5 40-5 20 5 40 5 20-5 40-5 20 5 40 5" stroke="#9fd8d6" stroke-width="2" fill="none" opacity=".6"/>
</svg>
