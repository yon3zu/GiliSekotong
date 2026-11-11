<x-layouts.admin title="SEO">
    @php
        $ok = collect($checks)->where(1, true)->count();
        $titleOf = fn ($p) => old("seo_page_{$p['key']}_title", $values["seo_page_{$p['key']}_title"]);
        $descOf = fn ($p) => old("seo_page_{$p['key']}_description", $values["seo_page_{$p['key']}_description"]);
    @endphp

    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <form action="{{ route('admin.seo.update') }}" method="post" enctype="multipart/form-data" class="min-w-0 space-y-6">
            @csrf @method('put')

            {{-- Umum --}}
            <section class="card space-y-4 p-6">
                <h2 class="font-display text-lg font-semibold">Umum</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="seo_site_name">Nama situs</label>
                        <input id="seo_site_name" name="seo_site_name" value="{{ old('seo_site_name', $values['seo_site_name']) }}" class="input" required>
                        <p class="mt-1 text-xs text-stone-500">Ditambahkan di akhir judul halaman detail perahu.</p>
                    </div>
                    <div>
                        <span class="label">Mesin pencari</span>
                        <label class="flex items-start gap-2 rounded-xl border border-sand-200 p-3 text-sm">
                            <input type="hidden" name="seo_indexing" value="0">
                            <input type="checkbox" name="seo_indexing" value="1" @checked(old('seo_indexing', $values['seo_indexing']) === '1') class="mt-0.5 size-4 rounded border-sand-300 text-sea-600">
                            <span>Izinkan Google mengindeks situs<span class="block text-xs text-stone-500">Matikan hanya untuk server uji/staging.</span></span>
                        </label>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-[240px_1fr] sm:items-start">
                    <img src="{{ \App\Support\Seo::ogImage() }}" alt="Gambar berbagi saat ini" class="aspect-[1200/630] w-full rounded-xl border border-sand-200 object-cover">
                    <div>
                        <label class="label" for="og_image">Gambar berbagi (Open Graph)</label>
                        <input id="og_image" type="file" name="og_image" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-sand-100 file:px-3 file:py-1.5 file:font-semibold">
                        <p class="mt-1 text-xs text-stone-500">Muncul saat link dibagikan di WhatsApp, Facebook, dan X. Ukuran ideal 1200×630 piksel. Kosongkan untuk memakai gambar bawaan GiliSekotong.</p>
                        @if ($values['seo_og_image'])
                            <label class="mt-2 flex items-center gap-2 text-xs"><input type="checkbox" name="remove_og_image" value="1" class="size-4 rounded border-sand-300"> Hapus gambar kustom</label>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Per halaman --}}
            <section class="card p-6">
                <h2 class="font-display text-lg font-semibold">Judul & deskripsi per halaman</h2>
                <p class="mt-1 text-sm text-stone-600">Ini yang tampil sebagai judul biru dan teks abu-abu di hasil Google. Ideal: judul 30–65 karakter, deskripsi 110–160 karakter, dan memuat kata yang dicari orang (mis. <em>sewa perahu Sekotong</em>, <em>Gili Nanggu</em>).</p>
                <div class="mt-5 space-y-5">
                    @foreach ($pages as $route => $page)
                        <div x-data="{ t: @js($titleOf($page)), d: @js($descOf($page)) }" class="rounded-xl border border-sand-200 p-4">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <p class="font-semibold">{{ $page['label'] }}</p>
                                <a href="{{ route($route) }}" target="_blank" class="text-xs text-sea-700 hover:underline">{{ \Illuminate\Support\Str::after(route($route), url('/')) ?: '/' }}</a>
                            </div>
                            <label class="mt-3 block">
                                <span class="flex justify-between text-xs font-semibold text-stone-600">Judul <span class="num" :class="t.length < 30 || t.length > 65 ? 'text-coral-700' : 'text-emerald-700'" x-text="t.length + ' karakter'"></span></span>
                                <input name="seo_page_{{ $page['key'] }}_title" x-model="t" maxlength="70" class="input mt-1" required>
                            </label>
                            <label class="mt-3 block">
                                <span class="flex justify-between text-xs font-semibold text-stone-600">Deskripsi <span class="num" :class="d.length < 110 || d.length > 160 ? 'text-coral-700' : 'text-emerald-700'" x-text="d.length + ' karakter'"></span></span>
                                <textarea name="seo_page_{{ $page['key'] }}_description" x-model="d" maxlength="170" rows="2" class="input mt-1" required></textarea>
                            </label>
                            {{-- pratinjau hasil Google --}}
                            <div class="mt-3 rounded-xl bg-white p-3 ring-1 ring-sand-200">
                                <p class="truncate text-xs text-stone-500">gilisekotong.com{{ \Illuminate\Support\Str::after(route($route), url('/')) }}</p>
                                <p class="truncate text-lg leading-snug text-[#1a0dab]" x-text="t"></p>
                                <p class="line-clamp-2 text-sm text-stone-600" x-text="d"></p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-4 text-xs text-stone-500">Halaman detail perahu memakai judul & deskripsi otomatis dari data perahu (nama, kapasitas, harga, deskripsi).</p>
            </section>

            {{-- Verifikasi & analitik --}}
            <section class="card grid gap-4 p-6 sm:grid-cols-3">
                <h2 class="font-display text-lg font-semibold sm:col-span-3">Verifikasi & analitik</h2>
                <div>
                    <label class="label" for="seo_google_verification">Google Search Console</label>
                    <input id="seo_google_verification" name="seo_google_verification" value="{{ old('seo_google_verification', $values['seo_google_verification']) }}" class="input" placeholder="kode verifikasi">
                    <p class="mt-1 text-xs text-stone-500">Dari tag HTML: isi atribut <code>content="…"</code> saja.</p>
                </div>
                <div>
                    <label class="label" for="seo_bing_verification">Bing Webmaster</label>
                    <input id="seo_bing_verification" name="seo_bing_verification" value="{{ old('seo_bing_verification', $values['seo_bing_verification']) }}" class="input" placeholder="kode verifikasi">
                </div>
                <div>
                    <label class="label" for="seo_ga4_id">Google Analytics 4</label>
                    <input id="seo_ga4_id" name="seo_ga4_id" value="{{ old('seo_ga4_id', $values['seo_ga4_id']) }}" class="input" placeholder="G-XXXXXXXXXX">
                </div>
            </section>

            {{-- Profil bisnis --}}
            <section class="card grid gap-4 p-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <h2 class="font-display text-lg font-semibold">Profil bisnis (structured data)</h2>
                    <p class="mt-1 text-sm text-stone-600">Membantu Google menampilkan GiliSekotong untuk pencarian lokal seperti "sewa perahu Sekotong". Samakan persis dengan Google Business Profile.</p>
                </div>
                <div>
                    <label class="label" for="biz_type">Jenis usaha</label>
                    <select id="biz_type" name="biz_type" class="input">
                        @foreach (['TravelAgency' => 'Agen perjalanan / sewa perahu wisata', 'LocalBusiness' => 'Usaha lokal (umum)', 'TouristInformationCenter' => 'Pusat informasi wisata'] as $k => $v)
                            <option value="{{ $k }}" @selected(old('biz_type', $values['biz_type']) === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="label" for="biz_price_range">Kisaran harga</label><input id="biz_price_range" name="biz_price_range" value="{{ old('biz_price_range', $values['biz_price_range']) }}" class="input"></div>
                <div class="sm:col-span-2"><label class="label" for="biz_street">Alamat jalan</label><input id="biz_street" name="biz_street" value="{{ old('biz_street', $values['biz_street']) }}" class="input"></div>
                <div><label class="label" for="biz_locality">Kecamatan</label><input id="biz_locality" name="biz_locality" value="{{ old('biz_locality', $values['biz_locality']) }}" class="input"></div>
                <div><label class="label" for="biz_region">Provinsi</label><input id="biz_region" name="biz_region" value="{{ old('biz_region', $values['biz_region']) }}" class="input"></div>
                <div><label class="label" for="biz_postal_code">Kode pos</label><input id="biz_postal_code" name="biz_postal_code" inputmode="numeric" value="{{ old('biz_postal_code', $values['biz_postal_code']) }}" class="input" placeholder="5 digit"></div>
                <div><label class="label" for="biz_opening_hours">Jam buka</label><input id="biz_opening_hours" name="biz_opening_hours" value="{{ old('biz_opening_hours', $values['biz_opening_hours']) }}" class="input"><p class="mt-1 text-xs text-stone-500">Format: Mo-Su 07:00-17:00</p></div>
                <div><label class="label" for="biz_lat">Latitude dermaga</label><input id="biz_lat" name="biz_lat" value="{{ old('biz_lat', $values['biz_lat']) }}" class="input num"></div>
                <div><label class="label" for="biz_lng">Longitude dermaga</label><input id="biz_lng" name="biz_lng" value="{{ old('biz_lng', $values['biz_lng']) }}" class="input num"><p class="mt-1 text-xs text-stone-500">Klik kanan titik dermaga di Google Maps untuk menyalin koordinatnya.</p></div>
                <div class="sm:col-span-2"><label class="label" for="biz_maps_url">Tautan Google Maps / Business Profile</label><input id="biz_maps_url" name="biz_maps_url" value="{{ old('biz_maps_url', $values['biz_maps_url']) }}" class="input" placeholder="https://maps.app.goo.gl/…"></div>
                <div><label class="label" for="biz_instagram">Instagram</label><input id="biz_instagram" name="biz_instagram" value="{{ old('biz_instagram', $values['biz_instagram']) }}" class="input" placeholder="https://instagram.com/…"></div>
                <div><label class="label" for="biz_facebook">Facebook</label><input id="biz_facebook" name="biz_facebook" value="{{ old('biz_facebook', $values['biz_facebook']) }}" class="input" placeholder="https://facebook.com/…"></div>
                <div><label class="label" for="biz_tiktok">TikTok</label><input id="biz_tiktok" name="biz_tiktok" value="{{ old('biz_tiktok', $values['biz_tiktok']) }}" class="input" placeholder="https://tiktok.com/@…"></div>
            </section>

            <button class="btn-primary">Simpan pengaturan SEO</button>
        </form>

        <aside class="space-y-6 xl:sticky xl:top-24 xl:self-start">
            <section class="card p-5">
                <div class="flex items-baseline justify-between">
                    <h2 class="font-display text-lg font-semibold">Daftar periksa</h2>
                    <span class="num text-sm font-semibold {{ $ok === count($checks) ? 'text-emerald-700' : 'text-stone-500' }}">{{ $ok }}/{{ count($checks) }}</span>
                </div>
                <ul class="mt-3 space-y-3 text-sm">
                    @foreach ($checks as [$label, $pass, $hint])
                        <li class="flex gap-2">
                            <span @class(['mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full', 'bg-emerald-100 text-emerald-700' => $pass, 'bg-coral-50 text-coral-700' => ! $pass])>
                                <x-icon :name="$pass ? 'check' : 'x'" class="size-3.5"/>
                            </span>
                            <span>{{ $label }}@unless ($pass)<span class="block text-xs text-stone-500">{{ $hint }}</span>@endunless</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="card space-y-2 p-5 text-sm">
                <h2 class="font-display text-lg font-semibold">Dibuat otomatis</h2>
                <p><a href="{{ route('sitemap') }}" target="_blank" class="font-semibold text-sea-700 hover:underline">sitemap.xml</a> <span class="text-stone-500">— semua halaman publik & perahu aktif</span></p>
                <p><a href="{{ route('robots') }}" target="_blank" class="font-semibold text-sea-700 hover:underline">robots.txt</a> <span class="text-stone-500">— admin, pemilik, pesanan, checkout tidak diindeks</span></p>
                <p class="text-stone-600">Juga: tag canonical, Open Graph, structured data usaha lokal, FAQ, perahu (harga), destinasi, dan breadcrumb.</p>
            </section>

            <section class="card space-y-2 p-5 text-sm leading-relaxed">
                <h2 class="font-display text-lg font-semibold">Agar muncul di Google</h2>
                <ol class="list-inside list-decimal space-y-1.5 text-stone-700">
                    <li>Pasang situs di <strong>gilisekotong.com</strong> dengan HTTPS.</li>
                    <li>Daftar di <strong>Google Search Console</strong>, verifikasi, lalu kirim <em>sitemap.xml</em>.</li>
                    <li>Buat <strong>Google Business Profile</strong> "GiliSekotong" di titik Dermaga Tawun. Ini paling berpengaruh untuk pencarian lokal dan Google Maps.</li>
                    <li>Minta tamu memberi <strong>ulasan Google</strong> setelah trip.</li>
                    <li>Pasang tautan gilisekotong.com di Instagram, TikTok, WhatsApp Business, dan situs wisata/desa.</li>
                    <li>Unggah <strong>foto asli</strong> perahu dan gili; perbarui konten secara rutin.</li>
                </ol>
                <p class="text-xs text-stone-500">Tidak ada yang bisa menjamin peringkat 1. Hasil biasanya mulai terlihat 2–8 minggu setelah situs terindeks.</p>
            </section>
        </aside>
    </div>
</x-layouts.admin>
