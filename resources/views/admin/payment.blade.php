<x-layouts.admin title="Pembayaran QRIS">
    <div class="grid max-w-3xl gap-6">
        <form action="{{ route('admin.payment.update') }}" method="post" class="card space-y-4 p-6">
            @csrf @method('put')
            <div>
                <h2 class="font-display text-lg font-semibold">QRcepat</h2>
                <p class="mt-1 text-sm text-stone-600">Pemesan bisa membayar dengan QRIS dan pesanan langsung terkonfirmasi tanpa verifikasi manual. Transfer bank tetap tersedia.</p>
            </div>
            <div>
                <label class="label" for="qris_api_key">Kunci API</label>
                <input id="qris_api_key" type="password" name="qris_api_key" class="input" autocomplete="new-password" placeholder="{{ $hasKey ? '•••••••• tersimpan, kosongkan bila tidak diganti' : 'qc_...' }}">
                <x-error name="qris_api_key"/>
                @if ($hasKey)<label class="mt-1 flex items-center gap-2 text-xs text-stone-600"><input type="checkbox" name="clear_key" value="1" class="size-4 rounded border-sand-300"> Hapus kunci tersimpan</label>@endif
                <p class="mt-1 text-xs text-stone-500">Dibuat di dashboard QRcepat, menu Pengaturan → API Keys. Disimpan terenkripsi dan tidak pernah ditampilkan ulang.</p>
            </div>
            <label class="flex items-start gap-2 rounded-xl border border-sand-200 p-3 text-sm">
                <input type="checkbox" name="qris_enabled" value="1" @checked($enabled) class="mt-0.5 size-4 rounded border-sand-300 text-sea-600">
                <span><span class="font-semibold">Aktifkan pembayaran QRIS</span><span class="block text-xs text-stone-500">Bila dimatikan, pemesan hanya melihat transfer bank.</span></span>
            </label>
            <button class="btn-primary">Simpan</button>
        </form>

        <form action="{{ route('admin.payment.test') }}" method="post" class="card flex flex-col gap-3 p-6 sm:flex-row sm:items-center sm:justify-between">
            @csrf
            <div>
                <h2 class="font-display text-lg font-semibold">Uji koneksi</h2>
                <p class="mt-1 text-sm text-stone-600">Memeriksa kunci API tanpa membuat tagihan.</p>
            </div>
            <button class="btn-ghost shrink-0">Uji kunci API</button>
        </form>

        <section class="rounded-2xl bg-sand-100 p-5 text-sm leading-relaxed text-stone-700">
            <p class="font-semibold text-sea-950">Perlu diketahui</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li>Fee mengikuti pengaturan akun QRcepat (ditanggung Anda atau pembeli). Nilainya tercatat di detail pesanan.</li>
                <li>Status lunas dicek otomatis tiap menit, jadi pastikan cron server berjalan.</li>
                <li>Bila QR dibayar setelah pesanan dibatalkan, sistem memberi catatan di pesanan agar dana dikembalikan.</li>
                <li>Satu transaksi QRIS maksimal Rp10.000.000.</li>
            </ul>
        </section>
    </div>
</x-layouts.admin>
