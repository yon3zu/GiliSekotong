<x-layouts.app title="Masuk">
    <div class="container-x grid max-w-5xl items-center gap-10 py-12 md:grid-cols-2 md:py-20">
        <div class="hidden md:block">
            <x-hero-art class="w-full rounded-3xl"/>
        </div>
        <div>
            <p class="eyebrow">Selamat datang kembali</p>
            <h1 class="h-display mt-2 text-4xl">Masuk</h1>
            <p class="mt-2 text-stone-600">Lihat pesanan, unggah bukti transfer, dan unduh e-tiket.</p>
            <div class="mt-6"><x-flash/></div>
            @if (session()->has('booking_draft'))
                <p class="mb-5 rounded-xl bg-sea-50 p-3 text-sm text-sea-900">Pesanan Anda masih tersimpan. Setelah masuk, Anda kembali ke checkout.</p>
            @endif
            <div class="card p-6">@include('auth._login-form')</div>
            <p class="mt-5 text-center text-sm text-stone-600">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-coral-600 hover:underline">Daftar gratis</a></p>
        </div>
    </div>
</x-layouts.app>
