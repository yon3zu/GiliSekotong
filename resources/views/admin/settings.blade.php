<x-layouts.admin title="Pengaturan">
    <form action="{{ route('admin.settings.update') }}" method="post" class="grid max-w-4xl gap-6 lg:grid-cols-2">
        @csrf @method('put')
        <section class="card space-y-4 p-6">
            <h2 class="font-display text-lg font-semibold">Harga & pembayaran</h2>
            <div><label class="label" for="payment_deadline_hours">Batas waktu bayar (jam)</label><input id="payment_deadline_hours" type="number" name="payment_deadline_hours" value="{{ old('payment_deadline_hours', $values['payment_deadline_hours']) }}" class="input"></div>
            <div>
                <label class="label" for="auto_complete_days">Selesaikan pesanan otomatis (hari setelah tanggal trip)</label>
                <input id="auto_complete_days" type="number" min="1" max="30" name="auto_complete_days" value="{{ old('auto_complete_days', $values['auto_complete_days']) }}" class="input">
                <p class="mt-1 text-xs text-stone-500">Pesanan di tanggal yang lautnya dinilai tidak layak tidak ikut diselesaikan, supaya bisa dijadwalkan ulang.</p>
            </div>
            <div><label class="label" for="bank_name">Bank</label><input id="bank_name" name="bank_name" value="{{ old('bank_name', $values['bank_name']) }}" class="input"></div>
            <div><label class="label" for="bank_account">Nomor rekening</label><input id="bank_account" name="bank_account" value="{{ old('bank_account', $values['bank_account']) }}" class="input"></div>
            <div><label class="label" for="bank_holder">Atas nama</label><input id="bank_holder" name="bank_holder" value="{{ old('bank_holder', $values['bank_holder']) }}" class="input"></div>
            <p class="text-xs text-stone-500">Harga sewa per perahu diatur di menu Perahu, paket makan di menu Paket Makan, dan alat (pelampung, snorkel, fins) di menu Sewa Alat.</p>
        </section>
        <section class="card space-y-4 p-6">
            <h2 class="font-display text-lg font-semibold">Kontak</h2>
            <div><label class="label" for="contact_phone">Telepon (tampilan)</label><input id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $values['contact_phone']) }}" class="input"></div>
            <div><label class="label" for="contact_whatsapp">WhatsApp (format 62…)</label><input id="contact_whatsapp" name="contact_whatsapp" value="{{ old('contact_whatsapp', $values['contact_whatsapp']) }}" class="input"></div>
            <div><label class="label" for="contact_email">Email</label><input id="contact_email" type="email" name="contact_email" value="{{ old('contact_email', $values['contact_email']) }}" class="input"></div>
            <div><label class="label" for="address">Alamat dermaga</label><textarea id="address" name="address" rows="3" class="input">{{ old('address', $values['address']) }}</textarea></div>
        </section>
        <div class="lg:col-span-2"><button class="btn-primary">Simpan pengaturan</button></div>
    </form>
</x-layouts.admin>
