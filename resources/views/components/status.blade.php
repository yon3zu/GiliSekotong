@props(['booking'])
@php
    $style = [
        'menunggu_pembayaran' => 'bg-sand-100 text-stone-700 ring-sand-300',
        'menunggu_verifikasi' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'terkonfirmasi' => 'bg-sea-50 text-sea-800 ring-sea-200',
        'selesai' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        'dibatalkan' => 'bg-red-50 text-red-700 ring-red-200',
    ][$booking->status] ?? 'bg-stone-100';
@endphp
<span {{ $attributes->merge(['class' => "chip ring-1 {$style}"]) }}>{{ $booking->statusLabel() }}</span>
