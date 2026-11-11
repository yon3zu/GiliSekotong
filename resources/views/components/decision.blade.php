@props(['value' => null, 'size' => 'sm'])
@php($tone = \App\Support\Sailing::tone($value))
<span {{ $attributes->merge(['class' => "chip tone-{$tone}".($size === 'lg' ? ' px-3.5 py-1 text-sm' : '')]) }}>
    <span @class(['size-1.5 rounded-full', 'bg-emerald-500' => $tone === 'layak', 'bg-amber-500' => $tone === 'waspada', 'bg-red-500' => $tone === 'tidak', 'bg-stone-400' => $tone === 'none'])></span>
    {{ $value ?? 'Belum ada data' }}
</span>
