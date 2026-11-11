<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-seo-head :title="$title ?? null" :description="$description ?? null" :image="$seoImage ?? null" :schema="$schema ?? []"/>
    <meta name="theme-color" content="#0c4448">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon-48.png') }}" sizes="48x48" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT,WONK@9..144,500..700,100,1&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh flex-col">
    @php
        $nav = [
            'boats.index' => 'Perahu',
            'destinations' => 'Destinasi',
            'sea' => 'Laut & Cuaca',
            'howto' => 'Cara Pesan',
            'about' => 'Tentang Kami',
            'contact' => 'Kontak',
        ];
        $hasDraft = session()->has('booking_draft');
    @endphp

    <header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-sand-200/80 bg-sand-50/90 backdrop-blur">
        <div class="container-x flex h-16 items-center justify-between gap-4">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <a href="{{ route('home') }}" aria-label="Beranda GiliSekotong"><x-logo/></a>
                <a href="{{ route('developers') }}" class="shrink-0 border-l border-sand-200 pl-2.5 sm:pl-3" title="AKSARA Universitas Bumigora, lihat tim pengembang">
                    <img src="{{ asset('images/aksara-badge.webp') }}" alt="AKSARA Universitas Bumigora" width="40" height="40" class="size-10 rounded-lg">
                </a>
            </div>

            <nav class="hidden items-center gap-0.5 xl:flex" aria-label="Menu utama">
                @foreach ($nav as $route => $label)
                    <a href="{{ route($route) }}" @class([
                        'rounded-full px-3 py-2 text-sm font-medium whitespace-nowrap transition',
                        'bg-sea-800 text-white' => request()->routeIs($route) || ($route === 'boats.index' && request()->routeIs('boats.*')),
                        'text-stone-700 hover:text-sea-800' => ! (request()->routeIs($route) || ($route === 'boats.index' && request()->routeIs('boats.*'))),
                    ])>{{ $label }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                @if ($hasDraft)
                    <a href="{{ route('booking.checkout') }}" class="relative hidden rounded-full p-2 text-sea-900 hover:bg-sand-100 sm:inline-flex" title="Lanjutkan pesanan">
                        <x-icon name="ticket"/>
                        <span class="absolute top-1 right-1 size-2 rounded-full bg-coral-500 ring-2 ring-sand-50"></span>
                    </a>
                @endif
                @auth
                    <a href="{{ auth()->user()->homeRoute() }}" class="btn-ghost btn-sm hidden sm:inline-flex">
                        {{ auth()->user()->isStaff() ? 'Panel Admin' : (auth()->user()->isOwner() ? 'Halaman Pemilik' : 'Pesanan Saya') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden text-sm font-semibold text-sea-900 hover:text-coral-600 sm:inline">Masuk</a>
                @endauth
                <a href="{{ route('boats.index') }}" class="btn-primary btn-sm hidden sm:inline-flex">Pesan Perahu</a>
                <button type="button" class="rounded-full p-2 text-sea-950 hover:bg-sand-100 xl:hidden" @click="open = !open" :aria-expanded="open" aria-label="Buka menu">
                    <x-icon name="menu" x-show="!open"/>
                    <x-icon name="x" x-show="open" x-cloak/>
                </button>
            </div>
        </div>

        <div x-show="open" x-cloak x-transition.origin.top class="border-t border-sand-200 bg-sand-50 xl:hidden">
            <nav class="container-x grid gap-1 py-4" aria-label="Menu seluler">
                <a href="{{ route('home') }}" class="rounded-xl px-3 py-2.5 font-medium hover:bg-sand-100">Beranda</a>
                @foreach ($nav as $route => $label)
                    <a href="{{ route($route) }}" class="rounded-xl px-3 py-2.5 font-medium hover:bg-sand-100">{{ $label }}</a>
                @endforeach
                <div class="mt-3 grid grid-cols-2 gap-2 border-t border-sand-200 pt-4">
                    @auth
                        <a href="{{ auth()->user()->homeRoute() }}" class="btn-ghost">{{ auth()->user()->isStaff() ? 'Panel Admin' : (auth()->user()->isOwner() ? 'Halaman Pemilik' : 'Pesanan Saya') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost">Masuk</a>
                    @endauth
                    <a href="{{ $hasDraft ? route('booking.checkout') : route('boats.index') }}" class="btn-primary">{{ $hasDraft ? 'Lanjut Pesan' : 'Pesan Perahu' }}</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="mt-20 bg-sea-950 text-sea-100">
        <div class="wave-edge h-3 -translate-y-px rotate-180"></div>
        <div class="container-x grid gap-10 py-14 md:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
            <div>
                <x-logo light/>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-sea-200">
                    Perahu nelayan Desa Sekotong Barat yang mengantar tamu ke Gili Nanggu, Gili Sudak, dan Gili Kedis sejak sebelum ada pelabuhan beton.
                </p>
            </div>
            <div>
                <p class="mb-3 text-sm font-bold text-white">Jelajahi</p>
                <ul class="space-y-2 text-sm">
                    <li><a class="hover:text-white" href="{{ route('boats.index') }}">Daftar perahu</a></li>
                    <li><a class="hover:text-white" href="{{ route('destinations') }}">Tiga gili</a></li>
                    <li><a class="hover:text-white" href="{{ route('sea') }}">Kondisi laut &amp; cuaca</a></li>
                    <li><a class="hover:text-white" href="{{ route('howto') }}">Cara pesan & bayar</a></li>
                </ul>
            </div>
            <div>
                <p class="mb-3 text-sm font-bold text-white">GiliSekotong</p>
                <ul class="space-y-2 text-sm">
                    <li><a class="hover:text-white" href="{{ route('about') }}">Tentang kami</a></li>
                    <li><a class="hover:text-white" href="{{ route('contact') }}">Kontak & FAQ</a></li>
                    <li><a class="hover:text-white" href="{{ route('developers') }}">Tim pengembang</a></li>
                    <li><a class="hover:text-white" href="{{ route('orders.index') }}">Pesanan saya</a></li>
                </ul>
            </div>
            <div class="text-sm">
                <p class="mb-3 font-bold text-white">Dermaga keberangkatan</p>
                <p class="flex gap-2 text-sea-200"><x-icon name="pin" class="mt-0.5 size-4 shrink-0"/>{{ \App\Models\Setting::get('address') }}</p>
                <p class="mt-2 flex gap-2 text-sea-200"><x-icon name="phone" class="mt-0.5 size-4 shrink-0"/>{{ \App\Models\Setting::get('contact_phone') }}</p>
                <p class="mt-2 flex gap-2 text-sea-200"><x-icon name="mail" class="mt-0.5 size-4 shrink-0"/>{{ \App\Models\Setting::get('contact_email') }}</p>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="container-x flex flex-col gap-2 py-5 text-xs text-sea-300 sm:flex-row sm:justify-between">
                <p>© {{ date('Y') }} GiliSekotong. Sekotong, Lombok Barat, NTB.</p>
                <p>Data cuaca, peringatan dini &amp; gempa: BMKG · Keputusan dibantu pohon keputusan C4.5, diputuskan nakhoda.</p>
            </div>
        </div>
    </footer>

    <a href="https://wa.me/{{ \App\Models\Setting::get('contact_whatsapp') }}" target="_blank" rel="noopener"
       class="fixed right-4 bottom-4 z-30 inline-flex size-12 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg shadow-black/15 transition hover:scale-105" aria-label="Chat WhatsApp">
        <x-icon name="whatsapp" class="size-6"/>
    </a>
</body>
</html>
