@props(['title' => 'Panel'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · Panel GiliSekotong</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT,WONK@9..144,500..700,100,1&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-sand-50" x-data="{ nav: false }">
    @php
        $user = auth()->user();
        $groups = [
            'Operasional' => [
                ['admin.dashboard', 'home', 'Ringkasan', 'admin.dashboard'],
                ['admin.bookings.index', 'ticket', 'Pemesanan', 'admin.bookings.*'],
                ['admin.blocks.index', 'alert', 'Tamu Offline', 'admin.blocks.*'],
                ['admin.checkin', 'scan', 'Check-in Dermaga', 'admin.checkin'],
                ['admin.owners.index', 'captain', 'Pemilik Kapal', 'admin.owners.*'],
                ['admin.reports.index', 'chart', 'Laporan', 'admin.reports.*'],
            ],
            'Konten' => [
                ['admin.media.index', 'sun', 'Galeri Beranda', 'admin.media.*'],
                ['admin.reviews.index', 'star', 'Ulasan', 'admin.reviews.*'],
            ],
            'Keputusan Pelayaran' => [
                ['admin.sea.index', 'wave', 'Kondisi Laut Harian', 'admin.sea.*'],
                ['admin.bmkg.index', 'cloud', 'Data BMKG', 'admin.bmkg.*'],
                ['admin.tree.index', 'tree', 'Pohon Keputusan', 'admin.tree.*'],
            ],
        ];
        if ($user->isSuperadmin()) {
            $groups['Keputusan Pelayaran'][] = ['admin.records.index', 'database', 'Data Latih', 'admin.records.*'];
            $groups['Master Data'] = [
                ['admin.boats.index', 'boat', 'Perahu', 'admin.boats.*'],
                ['admin.meals.index', 'food', 'Paket Makan', 'admin.meals.*'],
                ['admin.rentals.index', 'snorkel', 'Sewa Alat', 'admin.rentals.*'],
                ['admin.users.index', 'users', 'Pengguna', 'admin.users.*'],
                ['admin.developers.index', 'captain', 'Tim Pengembang', 'admin.developers.*'],
                ['admin.seo.edit', 'eye', 'SEO', 'admin.seo.*'],
                ['admin.payment.edit', 'ticket', 'Pembayaran QRIS', 'admin.payment.*'],
                ['admin.email.edit', 'mail', 'Email (SMTP)', 'admin.email.*'],
                ['admin.settings.edit', 'settings', 'Pengaturan', 'admin.settings.*'],
            ];
        }
        $pending = \App\Models\Booking::query()->where('status', 'menunggu_verifikasi')->count();
        $newBlocks = \App\Models\BoatBlock::query()->active()->where('created_at', '>=', now()->subDay())->count();
    @endphp

    <div x-show="nav" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-sea-950/50 lg:hidden" @click="nav = false"></div>

    <aside :class="nav ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-sea-950 text-sea-100 transition-transform lg:translate-x-0">
        <div class="flex h-16 items-center px-5"><a href="{{ route('home') }}"><x-logo light/></a></div>
        <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Menu panel">
            @foreach ($groups as $group => $items)
                <p class="mt-4 mb-2 px-3 text-[10px] font-bold tracking-[0.18em] text-sea-400 uppercase first:mt-0">{{ $group }}</p>
                @foreach ($items as [$route, $icon, $label, $pattern])
                    <a href="{{ route($route) }}" @class([
                        'flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition',
                        'bg-white/10 text-white' => request()->routeIs($pattern),
                        'hover:bg-white/5 hover:text-white' => ! request()->routeIs($pattern),
                    ])>
                        <x-icon :name="$icon" class="size-[18px]"/>
                        <span class="flex-1">{{ $label }}</span>
                        @if ($route === 'admin.blocks.index' && $newBlocks)
                            <span class="num rounded-full bg-coral-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $newBlocks }}</span>
                        @endif
                        @if ($route === 'admin.bookings.index' && $pending)
                            <span class="num rounded-full bg-coral-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $pending }}</span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-4">
            <p class="text-sm font-semibold text-white">{{ $user->name }}</p>
            <p class="text-xs text-sea-300">{{ $user->roleLabel() }}</p>
            <div class="mt-3 flex gap-2">
                <a href="{{ route('profile.edit') }}" class="btn-sm btn flex-1 bg-white/5 text-sea-100 hover:bg-white/10">Profil</a>
                <form action="{{ route('logout') }}" method="post" class="flex-1">@csrf<button class="btn-sm btn w-full bg-white/5 text-sea-100 hover:bg-white/10"><x-icon name="logout" class="size-3.5"/> Keluar</button></form>
            </div>
        </div>
    </aside>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-sand-200 bg-sand-50/90 px-4 backdrop-blur sm:px-6">
            <button type="button" class="rounded-lg p-1.5 hover:bg-sand-100 lg:hidden" @click="nav = true" aria-label="Buka menu"><x-icon name="menu"/></button>
            <h1 class="truncate font-display text-xl font-semibold text-sea-950">{{ $title }}</h1>
            <div class="ml-auto flex items-center gap-2">{{ $actions ?? '' }}</div>
        </header>
        <main class="px-4 py-6 sm:px-6 lg:px-8">
            <x-flash/>
            @if ($errors->any() && ! ($hideErrors ?? false))
                <div class="mb-6 rounded-xl border border-coral-100 bg-coral-50 px-4 py-3 text-sm text-coral-700">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</body>
</html>
