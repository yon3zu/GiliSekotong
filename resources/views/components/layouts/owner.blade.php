@props(['title' => 'Pemilik Kapal'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · GiliSekotong</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT,WONK@9..144,500..700,100,1&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Tampilan untuk pemilik kapal: huruf besar, tombol besar, menu bawah seperti aplikasi HP. --}}
<body class="bg-sand-50 pb-28 text-[17px] leading-relaxed">
    @php
        $menu = [
            ['owner.home', 'calendar', 'Jadwal'],
            ['owner.report', 'alert', 'Lapor Tamu'],
            ['owner.books', 'chart', 'Pembukuan'],
            ['owner.history', 'users', 'Penumpang'],
        ];
    @endphp
    <header class="bg-sea-900 text-white">
        <div class="mx-auto flex max-w-3xl items-center justify-between gap-3 px-4 py-4">
            <div>
                <p class="text-sm text-sea-200">Pemilik Kapal</p>
                <p class="font-display text-xl font-semibold">{{ auth()->user()->name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('owner.guide') }}" class="rounded-full bg-white/10 px-4 py-2 text-sm font-semibold">Panduan</a>
                <form action="{{ route('logout') }}" method="post">@csrf<button class="rounded-full bg-white/10 px-4 py-2 text-sm font-semibold">Keluar</button></form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-6">
        <h1 class="mb-5 font-display text-3xl font-semibold text-sea-950">{{ $title }}</h1>
        @foreach (['success' => 'bg-emerald-600 text-white'] as $key => $style)
            @if (session($key))
                <div class="mb-5 flex items-start gap-3 rounded-2xl p-4 text-lg font-semibold {{ $style }}" role="status">
                    <x-icon name="check" class="mt-1 size-6 shrink-0"/> <p>{{ session($key) }}</p>
                </div>
            @endif
        @endforeach
        {{ $slot }}
    </main>

    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-sand-200 bg-white" aria-label="Menu pemilik">
        <div class="mx-auto grid max-w-3xl grid-cols-4">
            @foreach ($menu as [$route, $icon, $label])
                <a href="{{ route($route) }}" @class([
                    'flex flex-col items-center gap-1 py-3 text-sm font-semibold',
                    'text-coral-600' => request()->routeIs($route),
                    'text-stone-600' => ! request()->routeIs($route),
                ])>
                    <x-icon :name="$icon" class="size-7"/>
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>
</body>
</html>
