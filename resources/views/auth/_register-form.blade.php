<form action="{{ route('register') }}" method="post" class="grid gap-4 sm:grid-cols-2">
    @csrf
    <div class="sm:col-span-2">
        <label for="reg_name" class="label">Nama lengkap</label>
        <input id="reg_name" name="name" value="{{ old('name') }}" required autocomplete="name" class="input">
        <x-error name="name"/>
    </div>
    <div>
        <label for="reg_email" class="label">Email</label>
        <input id="reg_email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="input">
        <x-error name="email"/>
    </div>
    <div>
        <label for="reg_phone" class="label">Nomor WhatsApp</label>
        <input id="reg_phone" name="phone" inputmode="tel" value="{{ old('phone') }}" required autocomplete="tel" class="input" placeholder="08xxxxxxxxxx">
        <x-error name="phone"/>
    </div>
    <div>
        <label for="reg_password" class="label">Kata sandi</label>
        <input id="reg_password" type="password" name="password" required autocomplete="new-password" class="input" minlength="8">
        <x-error name="password"/>
    </div>
    <div>
        <label for="reg_password_confirmation" class="label">Ulangi kata sandi</label>
        <input id="reg_password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="input">
    </div>
    <button class="btn-primary py-3 sm:col-span-2">Daftar & lanjutkan</button>
</form>
