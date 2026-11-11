<x-layouts.app title="Daftar">
    <div class="container-x max-w-2xl py-12 md:py-20">
        <p class="eyebrow">Akun pemesan</p>
        <h1 class="h-display mt-2 text-4xl">Daftar</h1>
        <p class="mt-2 text-stone-600">Satu akun untuk semua trip Anda bersama GiliSekotong.</p>
        @if (session()->has('booking_draft'))
            <p class="mt-5 rounded-xl bg-sea-50 p-3 text-sm text-sea-900">Pesanan Anda masih tersimpan. Setelah mendaftar, Anda langsung kembali ke checkout.</p>
        @endif
        <div class="card mt-6 p-6">@include('auth._register-form')</div>
        <p class="mt-5 text-center text-sm text-stone-600">Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-coral-600 hover:underline">Masuk</a></p>
    </div>
</x-layouts.app>
