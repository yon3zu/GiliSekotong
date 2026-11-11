<x-layouts.admin title="Email (SMTP)">
    <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
        <form action="{{ route('admin.email.update') }}" method="post" class="card grid max-w-3xl gap-4 self-start p-6 sm:grid-cols-2" x-data="{ enc: @js(old('mail_encryption', $values['mail_encryption'])) }">
            @csrf @method('put')
            <div class="sm:col-span-2">
                <h2 class="font-display text-lg font-semibold">Server SMTP</h2>
                <p class="mt-1 text-sm text-stone-600">Dipakai untuk mengirim email pesanan ke pemesan dan notifikasi ke admin.</p>
            </div>
            <div><label class="label" for="mail_host">Host SMTP</label><input id="mail_host" name="mail_host" value="{{ old('mail_host', $values['mail_host']) }}" class="input" placeholder="mail.gilisekotong.com"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label" for="mail_encryption">Enkripsi</label>
                    <select id="mail_encryption" name="mail_encryption" x-model="enc" @change="$refs.port.value = enc === 'ssl' ? 465 : (enc === 'tls' ? 587 : 25)" class="input">
                        <option value="tls">TLS (STARTTLS)</option><option value="ssl">SSL</option><option value="none">Tanpa</option>
                    </select>
                </div>
                <div><label class="label" for="mail_port">Port</label><input id="mail_port" x-ref="port" type="number" name="mail_port" value="{{ old('mail_port', $values['mail_port']) }}" class="input num"></div>
            </div>
            <div><label class="label" for="mail_username">Username</label><input id="mail_username" name="mail_username" value="{{ old('mail_username', $values['mail_username']) }}" class="input" autocomplete="off"></div>
            <div>
                <label class="label" for="mail_password">Password</label>
                <input id="mail_password" type="password" name="mail_password" class="input" autocomplete="new-password" placeholder="{{ $hasPassword ? '•••••••• tersimpan, kosongkan bila tetap' : '' }}">
                @if ($hasPassword)<label class="mt-1 flex items-center gap-2 text-xs text-stone-600"><input type="checkbox" name="clear_password" value="1" class="size-4 rounded border-sand-300"> Hapus password tersimpan</label>@endif
                <p class="mt-1 text-xs text-stone-500">Disimpan terenkripsi dan tidak pernah ditampilkan ulang. Untuk Gmail gunakan <em>App Password</em>.</p>
            </div>
            <div><label class="label" for="mail_from_address">Alamat pengirim</label><input id="mail_from_address" type="email" name="mail_from_address" value="{{ old('mail_from_address', $values['mail_from_address']) }}" class="input" required></div>
            <div><label class="label" for="mail_from_name">Nama pengirim</label><input id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $values['mail_from_name']) }}" class="input" required></div>
            <div class="sm:col-span-2"><label class="label" for="mail_admin_address">Email admin penerima notifikasi</label><input id="mail_admin_address" type="email" name="mail_admin_address" value="{{ old('mail_admin_address', $values['mail_admin_address']) }}" class="input" placeholder="kosong = memakai email kontak di Pengaturan"></div>
            <label class="flex items-start gap-2 rounded-xl border border-sand-200 p-3 text-sm sm:col-span-2">
                <input type="checkbox" name="mail_enabled" value="1" @checked(old('mail_enabled', $values['mail_enabled']) === '1') class="mt-0.5 size-4 rounded border-sand-300 text-sea-600">
                <span>Kirim email notifikasi otomatis
                    <span class="block text-xs text-stone-500">Ke pemesan: saat pesanan dibuat (instruksi bayar) dan saat e-tiket terbit. Ke admin: saat ada bukti transfer baru.</span></span>
            </label>
            <div class="sm:col-span-2"><button class="btn-primary">Simpan pengaturan email</button></div>
        </form>

        <aside class="space-y-6">
            <form action="{{ route('admin.email.test') }}" method="post" class="card space-y-3 p-5">
                @csrf
                <h2 class="font-display text-lg font-semibold">Kirim email uji</h2>
                <p class="text-sm text-stone-600">Status: @if ($configured)<span class="chip tone-layak">SMTP terisi</span>@else<span class="chip tone-none">Belum diatur</span>@endif</p>
                <input type="email" name="test_to" value="{{ old('test_to', auth()->user()->email) }}" class="input" required aria-label="Alamat tujuan email uji">
                <x-error name="test_to"/>
                <button class="btn-sea w-full">Kirim uji</button>
            </form>
            <section class="card space-y-2 p-5 text-sm leading-relaxed text-stone-700">
                <h2 class="font-display text-lg font-semibold">Contoh pengaturan</h2>
                <p><strong>Email di hosting gilisekotong.com:</strong> host <code>mail.gilisekotong.com</code>, SSL 465 atau TLS 587, username = alamat email lengkap.</p>
                <p><strong>Gmail / Google Workspace:</strong> <code>smtp.gmail.com</code>, TLS 587; aktifkan verifikasi 2 langkah lalu buat App Password.</p>
                <p class="text-xs text-stone-500">Supaya tidak masuk spam, pasang data SPF dan DKIM domain gilisekotong.com sesuai petunjuk penyedia email.</p>
            </section>
        </aside>
    </div>
</x-layouts.admin>
