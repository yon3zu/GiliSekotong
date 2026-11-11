{{-- Ilustrasi perahu katir khas Lombok, dipakai bila perahu belum punya foto. --}}
@props(['boat' => null, 'class' => ''])
@php
    $palettes = [['#e8683f', '#f5eee1', '#0d5a5a'], ['#188c8a', '#fbf8f2', '#cf4f27'], ['#f2b84b', '#0c4448', '#e8683f'], ['#2f6fb0', '#f5eee1', '#f2b84b'], ['#cf4f27', '#fbf8f2', '#188c8a']];
    [$hull, $stripe, $roof] = $palettes[($boat?->id ?? 0) % count($palettes)];
    $uid = 'b'.($boat?->id ?? 0).\Illuminate\Support\Str::random(4);
@endphp
<svg viewBox="0 0 400 260" {{ $attributes->merge(['class' => "block $class"]) }} role="img" aria-label="Ilustrasi {{ $boat?->name ?? 'perahu' }}" preserveAspectRatio="xMidYMid slice">
    <defs>
        <linearGradient id="{{ $uid }}s" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#cfeceb"/><stop offset="1" stop-color="#fbf8f2"/></linearGradient>
        <linearGradient id="{{ $uid }}w" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#3fa9a6"/><stop offset="1" stop-color="#0d5a5a"/></linearGradient>
    </defs>
    <rect width="400" height="260" fill="url(#{{ $uid }}s)"/>
    <circle cx="322" cy="62" r="26" fill="#f6c76b" opacity=".85"/>
    <path d="M0 150c40-14 70-30 112-30s70 20 98 30z" fill="#9fd8d6" opacity=".7"/>
    <path d="M250 152c18-22 40-34 62-34s46 16 70 34z" fill="#3fa9a6" opacity=".55"/>
    <rect y="148" width="400" height="112" fill="url(#{{ $uid }}w)"/>
    <g class="animate-bob">
        <path d="M86 176h240" stroke="#3b2a1a" stroke-width="3"/>
        <path d="M70 186c20-4 40-4 60 0M280 186c20-4 40-4 60 0" stroke="#e7d3b0" stroke-width="5" stroke-linecap="round" fill="none"/>
        <path d="M120 176l-30 12M290 176l30 12" stroke="#3b2a1a" stroke-width="2.5"/>
        <path d="M106 160h196c-8 22-22 30-44 30H142c-20 0-30-12-36-30z" fill="{{ $hull }}"/>
        <path d="M110 168h188" stroke="{{ $stripe }}" stroke-width="5"/>
        <path d="M96 150c4 6 8 10 14 10M314 146c-4 8-8 12-14 14" stroke="{{ $hull }}" stroke-width="8" stroke-linecap="round" fill="none"/>
        <rect x="150" y="122" width="108" height="8" rx="2" fill="{{ $roof }}"/>
        <path d="M156 130v30M252 130v30M204 130v30" stroke="#3b2a1a" stroke-width="2.5"/>
        <circle cx="178" cy="148" r="5" fill="#13262b"/><circle cx="228" cy="148" r="5" fill="#13262b"/>
    </g>
    <path d="M0 210c25 0 25-6 50-6s25 6 50 6 25-6 50-6 25 6 50 6 25-6 50-6 25 6 50 6 25-6 50-6 25 6 50 6" stroke="#cfeceb" stroke-width="2" fill="none" opacity=".6"/>
    <path d="M0 236c25 0 25-6 50-6s25 6 50 6 25-6 50-6 25 6 50 6 25-6 50-6 25 6 50 6 25-6 50-6 25 6 50 6" stroke="#cfeceb" stroke-width="2" fill="none" opacity=".35"/>
</svg>
