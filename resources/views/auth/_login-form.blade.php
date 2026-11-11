<form action="{{ route('login') }}" method="post" class="space-y-4">
    @csrf
    <div>
        <label for="login_email" class="label">Email atau nomor HP</label>
        <input id="login_email" type="text" name="login" value="{{ old('login') }}" required autocomplete="username" class="input" placeholder="nama@email.com / 0812xxxx">
        <x-error name="login"/>
    </div>
    <div>
        <label for="login_password" class="label">Kata sandi</label>
        <input id="login_password" type="password" name="password" required autocomplete="current-password" class="input">
    </div>
    <label class="flex items-center gap-2 text-sm text-stone-600">
        <input type="checkbox" name="remember" value="1" class="size-4 rounded border-sand-300 text-sea-600"> Ingat saya
    </label>
    <button class="btn-sea w-full py-3">Masuk</button>
</form>
