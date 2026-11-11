<x-layouts.app title="Profil">
    <div class="container-x max-w-3xl py-10">
        <a href="{{ auth()->user()->homeRoute() }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-sea-700"><x-icon name="back" class="size-4"/> Kembali</a>
        <h1 class="h-display mt-3 text-4xl">Profil</h1>
        <div class="mt-6"><x-flash/></div>
        <form action="{{ route('profile.update') }}" method="post" class="card grid gap-4 p-6 sm:grid-cols-2">
            @csrf @method('put')
            <div class="sm:col-span-2"><label class="label" for="name">Nama</label><input id="name" name="name" value="{{ old('name', $user->name) }}" class="input" required><x-error name="name"/></div>
            <div><label class="label" for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required><x-error name="email"/></div>
            <div><label class="label" for="phone">WhatsApp</label><input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="input"><x-error name="phone"/></div>
            <div class="sm:col-span-2"><button class="btn-sea">Simpan profil</button></div>
        </form>
        <form action="{{ route('profile.password') }}" method="post" class="card mt-6 grid gap-4 p-6 sm:grid-cols-3">
            @csrf @method('put')
            <h2 class="font-display text-lg font-semibold sm:col-span-3">Ganti kata sandi</h2>
            <div><label class="label" for="current_password">Sandi lama</label><input id="current_password" type="password" name="current_password" class="input" required><x-error name="current_password"/></div>
            <div><label class="label" for="password">Sandi baru</label><input id="password" type="password" name="password" class="input" required><x-error name="password"/></div>
            <div><label class="label" for="password_confirmation">Ulangi</label><input id="password_confirmation" type="password" name="password_confirmation" class="input" required></div>
            <div class="sm:col-span-3"><button class="btn-ghost">Ganti kata sandi</button></div>
        </form>
    </div>
</x-layouts.app>
